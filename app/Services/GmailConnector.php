<?php

namespace App\Services;

use App\Models\GmailConnection;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use SensitiveParameter;

class GmailConnector
{
    private const SCOPE = 'https://www.googleapis.com/auth/gmail.metadata';
    private const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const REVOKE_URL = 'https://oauth2.googleapis.com/revoke';
    private const MAILBOX_URL = 'https://gmail.googleapis.com/gmail/v1/users/me';
    private const THREAD_LIMIT = 5;

    public function configured(): bool
    {
        $url = (string) config('services.gmail.redirect_uri');
        $parts = parse_url($url);
        $secure = ($parts['scheme'] ?? '') === 'https';
        $loopback = ($parts['scheme'] ?? '') === 'http'
            && in_array($parts['host'] ?? '', ['localhost', '127.0.0.1', '[::1]'], true);

        return filled(config('services.gmail.client_id'))
            && filled(config('services.gmail.client_secret'))
            && filter_var($url, FILTER_VALIDATE_URL) !== false
            && ! isset($parts['user'])
            && ! isset($parts['pass'])
            && ! isset($parts['query'])
            && ! isset($parts['fragment'])
            && ($secure || ($loopback && app()->environment('local', 'testing')));
    }

    public function authorizationUrl(string $state, #[SensitiveParameter] string $verifier): string
    {
        return self::AUTH_URL . '?' . http_build_query([
            'client_id' => config('services.gmail.client_id'),
            'redirect_uri' => config('services.gmail.redirect_uri'),
            'response_type' => 'code',
            'scope' => self::SCOPE,
            'state' => $state,
            'access_type' => 'offline',
            'prompt' => 'consent select_account',
            'include_granted_scopes' => 'false',
            'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '='),
            'code_challenge_method' => 'S256',
        ], '', '&', PHP_QUERY_RFC3986);
    }

    public function connect(User $owner, #[SensitiveParameter] string $code, #[SensitiveParameter] string $verifier): void
    {
        Gate::forUser($owner)->authorize('connect-gmail');
        $response = $this->tokenRequest([
            'grant_type' => 'authorization_code',
            'redirect_uri' => config('services.gmail.redirect_uri'),
            'code' => $code,
            'code_verifier' => $verifier,
        ]);
        $tokens = $this->tokenData($response, requireRefresh: true);
        $profile = $this->readResponse($this->mailboxRequest($tokens['access_token'])->get(self::MAILBOX_URL . '/profile'));
        $emailAddress = $profile['emailAddress'] ?? null;

        if (! is_string($emailAddress) || ! filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            throw new GmailUnavailable('unavailable');
        }

        DB::transaction(function () use ($owner, $emailAddress, $tokens): void {
            $connection = GmailConnection::query()->updateOrCreate(
                ['owner_id' => $owner->id],
                [...$tokens, 'email_address' => $emailAddress, 'requires_reconnect' => false],
            );
            $this->audit($owner, $connection, 'connect');
        });
    }

    public function disconnect(User $owner): bool
    {
        Gate::forUser($owner)->authorize('connect-gmail');
        $token = DB::transaction(function () use ($owner): ?string {
            $connection = GmailConnection::query()->where('owner_id', $owner->id)->lockForUpdate()->first();

            if ($connection === null) {
                return null;
            }

            $this->audit($owner, $connection, 'disconnect');
            $connection->delete();

            return $connection->refresh_token;
        });

        if ($token === null) {
            return true;
        }

        try {
            $response = $this->request()->asForm()->post(self::REVOKE_URL, ['token' => $token]);

            return $response->successful() || $response->json('error') === 'invalid_token';
        } catch (ConnectionException) {
            return false;
        }
    }

    /** @return array{status: string, address: ?string, threads: list<array{subject: string, sender: string, url: string}>} */
    public function inbox(User $owner): array
    {
        Gate::forUser($owner)->authorize('connect-gmail');
        $connection = GmailConnection::query()->where('owner_id', $owner->id)->first();
        $result = ['address' => $connection?->email_address, 'threads' => []];

        if (! $this->configured()) {
            return ['status' => 'unconfigured', ...$result];
        }

        if ($connection === null) {
            return ['status' => 'disconnected', ...$result];
        }

        try {
            if ($connection->requires_reconnect) {
                return ['status' => 'reconnect', ...$result];
            }

            if ($connection->expires_at->lessThanOrEqualTo(now()->addMinute())) {
                $connection = $this->refresh($connection);
            }

            if ($connection === null || $connection->requires_reconnect) {
                return ['status' => 'reconnect', ...$result];
            }

            $token = $connection->access_token;
            $label = $this->actionLabel($token);

            if ($label === null) {
                return ['status' => 'label_missing', ...$result];
            }

            return [
                'status' => 'connected',
                ...$result,
                'threads' => $this->threads($token, $label, $connection->email_address),
            ];
        } catch (GmailUnavailable $exception) {
            if ($exception->getMessage() === 'reconnect' && $connection !== null) {
                GmailConnection::query()->whereKey($connection->id)
                    ->where('access_token', $connection->getRawOriginal('access_token'))
                    ->update(['requires_reconnect' => true]);
            }

            return ['status' => $exception->getMessage(), ...$result];
        } catch (ConnectionException) {
            return ['status' => 'unavailable', ...$result];
        }
    }

    private function refresh(GmailConnection $old): ?GmailConnection
    {
        return DB::transaction(function () use ($old): ?GmailConnection {
            $connection = GmailConnection::query()->lockForUpdate()->find($old->id);

            if (
                $connection === null
                || $connection->requires_reconnect
                || $connection->expires_at->isAfter(now()->addMinute())
            ) {
                return $connection;
            }

            $response = $this->tokenRequest([
                'grant_type' => 'refresh_token',
                'refresh_token' => $connection->refresh_token,
            ]);

            if ($response->json('error') === 'invalid_grant') {
                $connection->update(['requires_reconnect' => true]);

                return $connection;
            }

            $connection->update($this->tokenData($response, requireRefresh: false));

            return $connection;
        });
    }

    private function tokenRequest(#[SensitiveParameter] array $parameters): Response
    {
        return $this->request()->asForm()->post(self::TOKEN_URL, [
            'client_id' => config('services.gmail.client_id'),
            'client_secret' => config('services.gmail.client_secret'),
            ...$parameters,
        ]);
    }

    private function tokenData(Response $response, bool $requireRefresh): array
    {
        if (! $response->successful()) {
            throw new GmailUnavailable('unavailable');
        }

        $access = $response->json('access_token');
        $refresh = $response->json('refresh_token');
        $seconds = filter_var($response->json('expires_in'), FILTER_VALIDATE_INT);
        $scope = $response->json('scope');

        if (($requireRefresh || $scope !== null) && $scope !== self::SCOPE) {
            throw new GmailUnavailable($requireRefresh ? 'unavailable' : 'reconnect');
        }

        if (
            (! is_string($access) || $access === '')
            || ($requireRefresh && (! is_string($refresh) || $refresh === ''))
            || $seconds === false || $seconds < 1 || $seconds > 86400
        ) {
            throw new GmailUnavailable('unavailable');
        }

        return [
            'access_token' => $access,
            'expires_at' => now()->addSeconds($seconds),
            ...(is_string($refresh) && $refresh !== '' ? ['refresh_token' => $refresh] : []),
        ];
    }

    private function actionLabel(#[SensitiveParameter] string $token): ?string
    {
        $response = $this->readResponse($this->mailboxRequest($token)->get(self::MAILBOX_URL . '/labels', [
            'fields' => 'labels(id,name,type)',
        ]));

        foreach (($response['labels'] ?? []) as $label) {
            if (
                ($label['type'] ?? null) === 'user'
                && ($label['name'] ?? null) === config('services.gmail.label')
                && is_string($label['id'] ?? null)
            ) {
                return $label['id'];
            }
        }

        return null;
    }

    private function threads(#[SensitiveParameter] string $token, string $label, #[SensitiveParameter] string $emailAddress): array
    {
        $list = $this->readResponse($this->mailboxRequest($token)->get(self::MAILBOX_URL . '/threads', [
            'labelIds' => $label,
            'maxResults' => self::THREAD_LIMIT,
            'fields' => 'threads(id)',
        ]));
        $ids = array_values(array_filter(array_column($list['threads'] ?? [], 'id'), 'is_string'));
        $ids = array_slice($ids, 0, self::THREAD_LIMIT);

        if ($ids === []) {
            return [];
        }

        $responses = Http::pool(fn (Pool $pool): array => array_map(
            fn (string $id) => $this->mailboxRequest($token, $pool->as($id))->get(
                self::MAILBOX_URL . '/threads/' . rawurlencode($id),
                'format=metadata&metadataHeaders=Subject&metadataHeaders=From&fields=messages(labelIds,payload(headers))',
            ),
            $ids,
        ), concurrency: self::THREAD_LIMIT);
        $threads = [];

        foreach ($ids as $id) {
            $response = $responses[$id];

            if ($response instanceof Response && $response->notFound()) {
                continue;
            }

            $messages = $this->readResponse($response)['messages'] ?? [];

            foreach (array_reverse($messages) as $message) {
                if (! in_array($label, $message['labelIds'] ?? [], true)) {
                    continue;
                }

                $headers = array_change_key_case(array_column($message['payload']['headers'] ?? [], 'value', 'name'));
                $threads[] = [
                    'subject' => mb_substr((string) ($headers['subject'] ?? '(No subject)'), 0, 250),
                    'sender' => mb_substr((string) ($headers['from'] ?? 'Unknown sender'), 0, 250),
                    'url' => 'https://mail.google.com/mail/u/?authuser=' . rawurlencode($emailAddress) . '#all/' . rawurlencode($id),
                ];
                break;
            }
        }

        return $threads;
    }

    private function readResponse(Response|\Throwable $response): array
    {
        if ($response instanceof Response && $response->successful() && is_array($response->json())) {
            return $response->json();
        }

        throw new GmailUnavailable($response instanceof Response && $response->unauthorized() ? 'reconnect' : 'unavailable');
    }

    private function mailboxRequest(#[SensitiveParameter] string $token, ?PendingRequest $request = null): PendingRequest
    {
        return $this->request($request)->withToken($token);
    }

    private function request(?PendingRequest $request = null): PendingRequest
    {
        return ($request ?? Http::acceptJson())->acceptJson()->connectTimeout(2)->timeout(5)->withoutRedirecting();
    }

    private function audit(User $owner, GmailConnection $connection, string $operation): void
    {
        DB::table('audit_events')->insert([
            'actor_id' => $owner->id,
            'operation' => $operation,
            'target_type' => 'gmail_connection',
            'target_id' => $connection->id,
            'outcome' => 'succeeded',
            'occurred_at' => now(),
        ]);
    }
}

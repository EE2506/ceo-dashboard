<?php

namespace Tests\Feature;

use App\Models\GmailConnection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GmailConnectorTest extends TestCase
{
    use RefreshDatabase;

    private const PROFILE = 'https://gmail.googleapis.com/gmail/v1/users/me/profile';
    private const TOKEN = 'https://oauth2.googleapis.com/token';
    private const LABELS = 'https://gmail.googleapis.com/gmail/v1/users/me/labels*';
    private const THREADS = 'https://gmail.googleapis.com/gmail/v1/users/me/threads';
    private const SCOPE = 'https://www.googleapis.com/auth/gmail.metadata';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.gmail.client_id' => 'synthetic-client-id',
            'services.gmail.client_secret' => 'synthetic-client-secret',
            'services.gmail.redirect_uri' => 'http://127.0.0.1:8021/admin/gmail/callback',
        ]);
        Http::preventStrayRequests();
    }

    public function test_owner_connects_and_reads_labeled_metadata_without_persisting_messages(): void
    {
        $owner = User::factory()->owner()->create();
        $state = $this->startConnection($owner);
        $handshake = session('gmail.oauth');

        Http::fake([
            self::TOKEN => Http::response([
                'access_token' => 'synthetic-access',
                'refresh_token' => 'synthetic-refresh',
                'expires_in' => 3600,
                'scope' => self::SCOPE,
            ]),
            self::PROFILE => Http::response(['emailAddress' => 'owner@example.test']),
            self::LABELS => Http::response(['labels' => [['id' => 'Label_1', 'name' => 'Needs action', 'type' => 'user']]]),
            self::THREADS . '?*' => Http::response(['threads' => [['id' => 'sample-thread']]]),
            self::THREADS . '/sample-thread*' => Http::response(['messages' => [
                [
                    'labelIds' => ['Label_1'],
                    'payload' => [
                        'headers' => [
                            ['name' => 'Subject', 'value' => '<b>Check the renewal</b>'],
                            ['name' => 'From', 'value' => 'Example sender <client@example.test>'],
                        ],
                        'body' => ['data' => 'synthetic-private-body'],
                    ],
                ],
                [
                    'labelIds' => ['INBOX'],
                    'payload' => ['headers' => [['name' => 'Subject', 'value' => 'Newer unlabeled subject']]],
                ],
            ]]),
        ]);

        $this->get(route('gmail.callback', ['state' => 'wrong', 'code' => 'example-code']))
            ->assertSessionHas('gmail.status', 'invalid_state');
        Http::assertNothingSent();

        $this->get(route('gmail.callback', ['state' => $state, 'code' => 'example-code']))
            ->assertRedirect('/admin/gmail')->assertSessionHas('gmail.status', 'connected');
        Http::assertSent(fn (Request $request): bool => $request->url() === self::TOKEN
            && $request['code_verifier'] === $handshake['verifier']);
        $connection = GmailConnection::query()->sole();
        $this->assertSame('synthetic-refresh', $connection->refresh_token);
        $this->assertNotSame('synthetic-refresh', $connection->getRawOriginal('refresh_token'));
        $this->assertNotSame('synthetic-access', $connection->getRawOriginal('access_token'));
        $this->assertNotSame('owner@example.test', $connection->getRawOriginal('email_address'));
        $this->assertDatabaseHas('audit_events', [
            'actor_id' => $owner->id,
            'client_id' => null,
            'operation' => 'connect',
            'target_type' => 'gmail_connection',
        ]);

        $page = $this->get('/admin/gmail')->assertOk()
            ->assertSee('owner@example.test')
            ->assertSee('<b>Check the renewal</b>')
            ->assertDontSee('<b>Check the renewal</b>', false)
            ->assertDontSee('Newer unlabeled subject')
            ->assertDontSee('synthetic-private-body');
        $this->assertStringContainsString('no-store', $page->headers->get('Cache-Control'));
        Http::assertSent(fn (Request $request): bool => str_starts_with($request->url(), self::THREADS . '?')
            && str_contains($request->url(), 'labelIds=Label_1')
            && ! str_contains($request->url(), '&q='));
        Http::assertSent(fn (Request $request): bool => str_starts_with($request->url(), self::THREADS . '/sample-thread')
            && str_contains($request->url(), 'format=metadata')
            && str_contains($request->url(), 'metadataHeaders=Subject')
            && $request->hasHeader('Authorization', 'Bearer synthetic-access'));

        $this->get(route('gmail.callback', ['state' => $state, 'code' => 'reused-code']))
            ->assertSessionHas('gmail.status', 'invalid_state');
        Http::assertSentCount(5);
    }

    public function test_nonowners_and_broader_oauth_grants_are_rejected(): void
    {
        Http::fake([
            self::TOKEN => Http::response([
                'access_token' => 'synthetic-access',
                'refresh_token' => 'synthetic-refresh',
                'expires_in' => 3600,
                'scope' => 'https://www.googleapis.com/auth/gmail.modify',
            ]),
        ]);

        $this->get('/admin/gmail')->assertRedirect('/admin/login');
        $this->post(route('gmail.connect'))->assertForbidden();
        $this->actingAs(User::factory()->create())->get('/admin/gmail')->assertForbidden();
        $this->post(route('gmail.disconnect'))->assertForbidden();
        $this->get(route('gmail.callback'))->assertForbidden();
        Http::assertNothingSent();

        $state = $this->startConnection(User::factory()->owner()->create());
        $this->get(route('gmail.callback', ['state' => $state, 'code' => 'example-code']))
            ->assertSessionHas('gmail.status', 'connection_failed');
        $this->assertDatabaseCount('gmail_connections', 0);
        Http::assertSentCount(1);
    }

    public function test_refresh_preserves_the_existing_refresh_token_and_reports_a_missing_label(): void
    {
        $owner = User::factory()->owner()->create();
        $connection = $this->connection($owner);
        Http::fake([
            self::TOKEN => Http::response(['access_token' => 'replacement-access', 'expires_in' => 3600]),
            self::LABELS => Http::response(['labels' => []]),
        ]);

        $this->actingAs($owner)->get('/admin/gmail')->assertOk()->assertSee('Create the label');
        $this->assertSame('replacement-access', $connection->refresh()->access_token);
        $this->assertSame('synthetic-refresh', $connection->refresh_token);
        Http::assertSent(fn (Request $request): bool => $request->url() === self::TOKEN
            && $request['grant_type'] === 'refresh_token'
            && $request['refresh_token'] === 'synthetic-refresh');
    }

    public function test_revoked_access_is_reported_once_and_disconnect_audits_local_removal(): void
    {
        $owner = User::factory()->owner()->create();
        $connection = $this->connection($owner);
        Http::fake([
            self::TOKEN => Http::response(['error' => 'invalid_grant'], 400),
            'https://oauth2.googleapis.com/revoke' => Http::response([], 503),
        ]);

        $this->actingAs($owner)->get('/admin/gmail')->assertOk()->assertSee('Reconnect Gmail');
        $this->assertTrue($connection->refresh()->requires_reconnect);
        $this->get('/admin/gmail')->assertOk()->assertSee('Reconnect Gmail');
        Http::assertSentCount(1);

        $this->post(route('gmail.disconnect'))->assertSessionHas('gmail.status', 'revoke_unconfirmed');
        $this->assertDatabaseCount('gmail_connections', 0);
        $this->assertDatabaseHas('audit_events', [
            'actor_id' => $owner->id,
            'target_id' => $connection->id,
            'operation' => 'disconnect',
        ]);
    }

    public function test_a_transient_provider_error_is_not_reported_as_an_empty_inbox(): void
    {
        $owner = User::factory()->owner()->create();
        $connection = $this->connection($owner);
        $connection->update(['expires_at' => now()->addHour()]);
        Http::fake([self::LABELS => Http::response([], 503)]);

        $this->actingAs($owner)->get('/admin/gmail')->assertOk()
            ->assertSee('Gmail is temporarily unavailable')
            ->assertDontSee('No Gmail threads have this label');
        $this->assertFalse($connection->refresh()->requires_reconnect);
    }

    private function startConnection(User $owner): string
    {
        $response = $this->actingAs($owner)->post(route('gmail.connect'))->assertRedirect();
        $url = $response->headers->get('Location');
        $this->assertStringStartsWith('https://accounts.google.com/o/oauth2/v2/auth?', $url);
        parse_str(parse_url($url, PHP_URL_QUERY), $parameters);
        $this->assertSame(self::SCOPE, $parameters['scope']);
        $this->assertSame('offline', $parameters['access_type']);
        $this->assertSame('S256', $parameters['code_challenge_method']);
        $this->assertArrayNotHasKey('client_secret', $parameters);

        return $parameters['state'];
    }

    private function connection(User $owner): GmailConnection
    {
        return GmailConnection::create([
            'owner_id' => $owner->id,
            'email_address' => 'owner@example.test',
            'access_token' => 'synthetic-access',
            'refresh_token' => 'synthetic-refresh',
            'expires_at' => now()->subMinute(),
        ])->refresh();
    }
}

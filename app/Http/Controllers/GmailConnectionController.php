<?php

namespace App\Http\Controllers;

use App\Filament\Pages\Gmail;
use App\Services\GmailConnector;
use App\Services\GmailUnavailable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GmailConnectionController extends Controller
{
    private const HANDSHAKE = 'gmail.oauth';

    public function authorizeMailbox(Request $request, GmailConnector $connector): RedirectResponse
    {
        if (! $connector->configured()) {
            return $this->backToInbox('unconfigured');
        }

        $verifier = Str::random(64);
        $state = Str::random(48);
        $request->session()->put(self::HANDSHAKE, [
            'owner' => $request->user()->id,
            'state' => $state,
            'verifier' => $verifier,
            'expires_at' => now()->addMinutes(10)->getTimestamp(),
        ]);

        return redirect()->away($connector->authorizationUrl($state, $verifier));
    }

    public function callback(Request $request, GmailConnector $connector): RedirectResponse
    {
        $handshake = $request->session()->get(self::HANDSHAKE);
        $state = $request->query('state');

        if (
            ! is_array($handshake)
            || ! is_string($state)
            || $handshake['owner'] !== $request->user()->id
            || $handshake['expires_at'] <= now()->getTimestamp()
            || ! hash_equals($handshake['state'], $state)
        ) {
            return $this->backToInbox('invalid_state');
        }

        $request->session()->forget(self::HANDSHAKE);
        $code = $request->query('code');

        if ($request->query->has('error') || ! is_string($code) || $code === '' || strlen($code) > 2048) {
            return $this->backToInbox('cancelled');
        }

        if (! $connector->configured()) {
            return $this->backToInbox('unconfigured');
        }

        try {
            $connector->connect($request->user(), $code, $handshake['verifier']);
        } catch (ConnectionException | GmailUnavailable) {
            return $this->backToInbox('connection_failed');
        }

        return $this->backToInbox('connected');
    }

    public function disconnect(Request $request, GmailConnector $connector): RedirectResponse
    {
        $request->session()->forget(self::HANDSHAKE);
        $revoked = $connector->disconnect($request->user());

        return $this->backToInbox($revoked ? 'disconnected' : 'revoke_unconfirmed');
    }

    private function backToInbox(string $status): RedirectResponse
    {
        return redirect()->to(Gmail::getUrl(panel: 'admin'))
            ->with('gmail.status', $status)
            ->header('Cache-Control', 'no-store, private')
            ->header('Referrer-Policy', 'no-referrer');
    }
}

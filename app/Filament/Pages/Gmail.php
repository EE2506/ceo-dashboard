<?php

namespace App\Filament\Pages;

use App\Services\GmailConnector;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;

class Gmail extends Page
{
    protected static ?string $title = 'Gmail';

    protected static ?string $navigationLabel = 'Gmail';

    protected static ?string $slug = 'gmail';

    protected static ?int $navigationSort = 7;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|array $routeMiddleware = ['cache.headers:private;no_store;max_age=0'];

    protected string $view = 'filament.pages.gmail';

    public static function canAccess(): bool
    {
        return Gate::allows('connect-gmail');
    }

    protected function getViewData(): array
    {
        return [
            'inbox' => app(GmailConnector::class)->inbox(auth()->user()),
            'label' => config('services.gmail.label'),
            'feedback' => match (session('gmail.status')) {
                'connected' => 'Gmail connected.',
                'disconnected' => 'Gmail disconnected.',
                'revoke_unconfirmed' => 'Local access was removed. Check your Google Account permissions to confirm revocation.',
                'cancelled' => 'Gmail connection was cancelled.',
                'invalid_state' => 'This connection attempt expired or is invalid. Start again from this page.',
                'connection_failed' => 'Gmail could not be connected. Check the Google OAuth setup and try again.',
                'unconfigured' => 'Gmail OAuth settings are missing or invalid.',
                default => null,
            },
        ];
    }
}

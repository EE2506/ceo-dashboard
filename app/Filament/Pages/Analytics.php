<?php

namespace App\Filament\Pages;

use App\Models\FollowUp;
use App\Models\Payment;
use App\Models\Site;
use App\Services\DashboardAnalytics;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;

class Analytics extends Page
{
    protected static ?int $navigationSort = 6;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected string $view = 'filament.pages.analytics';

    public static function canAccess(): bool
    {
        return Gate::allows('viewAny', Payment::class)
            && Gate::allows('viewAny', Site::class)
            && Gate::allows('viewAny', FollowUp::class);
    }

    protected function getViewData(): array
    {
        $today = now('Asia/Manila');
        $analytics = app(DashboardAnalytics::class);

        return [
            'today' => $today,
            'snapshot' => $analytics->snapshot($today),
            'months' => $analytics->monthlyPaid($today),
        ];
    }
}

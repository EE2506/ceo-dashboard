<?php

namespace App\Filament\Pages;

use App\Filament\Resources\FollowUpResource;
use App\Filament\Resources\PaymentResource;
use App\Models\FollowUp;
use App\Models\Payment;
use App\Services\TodayPriorities;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;

class Today extends Page
{
    protected static ?int $navigationSort = 0;

    protected string $view = 'filament.pages.today';

    public static function canAccess(): bool
    {
        return Gate::allows('viewAny', FollowUp::class) && Gate::allows('viewAny', Payment::class);
    }

    protected function getViewData(): array
    {
        $today = now('Asia/Manila');
        $date = $today->toDateString();
        $items = app(TodayPriorities::class)->forDate($date);

        foreach ($items as $index => $item) {
            $resource = $item['type'] === 'payment' ? PaymentResource::class : FollowUpResource::class;
            $items[$index]['url'] = $resource::getUrl(parameters: [
                'tableAction' => 'edit',
                'tableActionRecord' => $item['id'],
            ]);
        }
        return ['today' => $today, 'items' => $items];
    }
}

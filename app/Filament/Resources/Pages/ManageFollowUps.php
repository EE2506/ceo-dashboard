<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\FollowUpResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageFollowUps extends ManageRecords
{
    protected static string $resource = FollowUpResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}

<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\SiteChangeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSiteChanges extends ManageRecords
{
    protected static string $resource = SiteChangeResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}

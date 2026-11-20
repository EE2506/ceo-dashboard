<?php

namespace App\Filament\Resources;

use App\Filament\HubFields;
use App\Filament\Resources\Pages\ManageSites;
use App\Models\Client;
use App\Models\Site;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SiteResource extends Resource
{
    protected static ?string $model = Site::class;

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            HubFields::client()->disabledOn('edit'),
            TextInput::make('name')->label('Site name')->required()->rules(['string'])->maxLength(160),
            Textarea::make('description')->rules(['string'])->maxLength(4000)->columnSpanFull(),
            TextInput::make('technologies')->rules(['string'])->maxLength(500)->columnSpanFull(),
            HubFields::url('repo_url')->label('Repository link'),
            HubFields::url('hosting_url')->label('Hosting panel link'),
            HubFields::url('design_url')->label('Design link'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Site'),
                TextColumn::make('client.name')->label('Client'),
                TextColumn::make('technologies')->limit(60),
            ])
            ->filters([
                SelectFilter::make('client_id')->label('Client')
                    ->options(fn (): array => Client::query()->pluck('name', 'id')->all()),
            ])
            ->defaultSort('id', 'desc')
            ->paginated([20, 50])
            ->defaultPaginationPageOption(20)
            ->recordActions([
                Action::make('changeLog')->label('Change log')->url(fn (Site $record): string =>
                    SiteChangeResource::getUrl(parameters: ['tableFilters' => ['site_id' => ['value' => $record->id]]])),
                Action::make('repository')->url(fn (Site $record): ?string => $record->repo_url, true)
                    ->visible(fn (Site $record): bool => filled($record->repo_url)),
                Action::make('hosting')->url(fn (Site $record): ?string => $record->hosting_url, true)
                    ->visible(fn (Site $record): bool => filled($record->hosting_url)),
                Action::make('design')->url(fn (Site $record): ?string => $record->design_url, true)
                    ->visible(fn (Site $record): bool => filled($record->design_url)),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageSites::route('/')];
    }
}

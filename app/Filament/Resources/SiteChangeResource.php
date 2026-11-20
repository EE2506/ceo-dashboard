<?php

namespace App\Filament\Resources;

use App\Filament\HubFields;
use App\Filament\Resources\Pages\ManageSiteChanges;
use App\Models\Site;
use App\Models\SiteChange;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SiteChangeResource extends Resource
{
    protected static ?string $model = SiteChange::class;

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Change-log vault';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            HubFields::client(),
            HubFields::site()->required(),
            HubFields::date('changed_on')->label('Date')->default(fn (): string => now('Asia/Manila')->toDateString())->required(),
            TextInput::make('title')->required()->rules(['string'])->maxLength(160),
            Textarea::make('description')->label('What changed')->required()->rules(['string'])->maxLength(4000)->columnSpanFull(),
            Textarea::make('reason')->label('Why it changed')->required()->rules(['string'])->maxLength(4000)->columnSpanFull(),
            Repeater::make('links')->simple(HubFields::url('link')->required())->defaultItems(0)->maxItems(10)->columnSpanFull(),
            HubFields::visibility(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('changed_on')->label('Date')->date('M j, Y')->sortable(),
                TextColumn::make('title'),
                TextColumn::make('site.name')->label('Site'),
                TextColumn::make('client.name')->label('Client'),
                TextColumn::make('is_client_visible')->label('Visibility')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Client-visible' : 'Internal'),
            ])
            ->filters([
                SelectFilter::make('site_id')->label('Site')->options(
                    fn (): array => Site::query()->with('client')->get()
                        ->mapWithKeys(fn (Site $site): array => [$site->id => $site->client->name.' · '.$site->name])->all()
                ),
            ])
            ->defaultSort('changed_on', 'desc')
            ->paginated([20, 50])
            ->defaultPaginationPageOption(20)
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageSiteChanges::route('/')];
    }
}

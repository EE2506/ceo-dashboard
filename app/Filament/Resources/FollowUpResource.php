<?php

namespace App\Filament\Resources;

use App\Filament\HubFields;
use App\Filament\Resources\Pages\ManageFollowUps;
use App\Models\FollowUp;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FollowUpResource extends Resource
{
    protected static ?string $model = FollowUp::class;

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            HubFields::client(),
            HubFields::site(),
            TextInput::make('title')->required()->rules(['string'])->maxLength(160)->columnSpanFull(),
            HubFields::date('due_on')->label('Due date')
                ->default(fn (): string => now('Asia/Manila')->toDateString())->required(),
            Toggle::make('is_completed')->label('Completed')->default(false)->rules(['boolean']),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title'),
                TextColumn::make('client.name')->label('Client'),
                TextColumn::make('site.name')->label('Site'),
                TextColumn::make('due_on')->label('Due date')->date('M j, Y')->sortable(),
                TextColumn::make('is_completed')->label('Status')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Completed' : 'Open'),
            ])
            ->defaultSort('due_on')
            ->paginated([20, 50])
            ->defaultPaginationPageOption(20)
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageFollowUps::route('/')];
    }
}

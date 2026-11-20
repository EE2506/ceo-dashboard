<?php

namespace App\Filament\Resources;

use App\Filament\HubFields;
use App\Filament\Resources\Pages\ManagePayments;
use App\Models\Payment;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            HubFields::client(),
            HubFields::site()->placeholder('Client-level payment'),
            HubFields::date('recorded_on')->label('Date')->default(fn (): string => now('Asia/Manila')->toDateString())->required(),
            HubFields::date('due_on')->label('Due date'),
            TextInput::make('amount')->prefix('₱')->inputMode('decimal')->required()
                ->extraInputAttributes(['maxlength' => 13])
                ->rules(['regex:/\A\d{1,10}(?:\.\d{1,2})?\z/', 'numeric', 'gt:0'])
                ->validationMessages(['regex' => 'Enter a peso amount with up to two decimal places.']),
            Select::make('status')->options(Payment::STATUSES)->in(array_keys(Payment::STATUSES))->default('unpaid')->required(),
            Textarea::make('purpose')->required()->rules(['string'])->maxLength(2000)->columnSpanFull(),
            HubFields::url('invoice_url')->label('Invoice link'),
            HubFields::visibility(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('recorded_on')->label('Date')->date('M j, Y')->sortable(),
                TextColumn::make('client.name')->label('Client'),
                TextColumn::make('site.name')->label('Site')->placeholder('Client-level'),
                TextColumn::make('amount')->money('PHP', locale: 'en_PH')->sortable(),
                TextColumn::make('status')->formatStateUsing(fn (string $state): string => Payment::STATUSES[$state]),
                TextColumn::make('due_on')->label('Due')->date('M j, Y')->sortable(),
                TextColumn::make('is_client_visible')->label('Visibility')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Client-visible' : 'Internal'),
            ])
            ->filters([
                SelectFilter::make('status')->options(Payment::STATUSES),
            ])
            ->defaultSort('recorded_on', 'desc')
            ->paginated([20, 50])
            ->defaultPaginationPageOption(20)
            ->recordActions([
                Action::make('invoice')->url(fn (Payment $record): ?string => $record->invoice_url, true)
                    ->visible(fn (Payment $record): bool => filled($record->invoice_url)),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManagePayments::route('/')];
    }
}

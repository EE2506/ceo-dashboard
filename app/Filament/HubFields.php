<?php

namespace App\Filament;

use App\Models\Client;
use App\Models\Site;
use App\Rules\ReferenceUrl;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Validation\Rule;

class HubFields
{
    public static function client(): Select
    {
        return Select::make('client_id')
            ->label('Client')
            ->options(fn (): array => Client::query()->orderBy('id')->pluck('name', 'id')->all())
            ->searchable()
            ->required()
            ->exists('clients', 'id')
            ->live()
            ->afterStateUpdated(fn (Set $set) => $set('site_id', null));
    }

    public static function site(): Select
    {
        return Select::make('site_id')
            ->label('Site')
            ->options(fn (Get $get): array => Site::query()
                ->where('client_id', $get('client_id'))
                ->orderBy('id')
                ->pluck('name', 'id')
                ->all())
            ->searchable()
            ->disabled(fn (Get $get): bool => blank($get('client_id')))
            ->rules(fn (Get $get): array => [
                Rule::exists('sites', 'id')->where('client_id', $get('client_id')),
            ]);
    }

    public static function url(string $name): TextInput
    {
        return TextInput::make($name)
            ->url()
            ->maxLength(2048)
            ->rule(new ReferenceUrl());
    }

    public static function date(string $name): DatePicker
    {
        return DatePicker::make($name)
            ->format('Y-m-d')
            ->rules(['date_format:Y-m-d', 'after_or_equal:1000-01-01', 'before_or_equal:9999-12-31']);
    }

    public static function visibility(): Toggle
    {
        return Toggle::make('is_client_visible')
            ->label('Mark client-visible')
            ->helperText('Reserved for the future client portal.')
            ->default(false)
            ->rules(['boolean']);
    }
}

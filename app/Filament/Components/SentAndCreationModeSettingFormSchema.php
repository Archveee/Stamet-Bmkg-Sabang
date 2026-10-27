<?php

namespace App\Filament\Components;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Section;

class SentAndCreationModeSettingFormSchema
{
    public static function make()
    {
        return [
            self::makeSection('SINOPTIK', 'bmkgsatu_sinoptik'),
            self::makeSection('METAR', 'bmkgsatu_metar'),
            self::makeSection('WXREV', 'bmkgsatu_wxrev'),
            self::makeSection('DATA CUACA HARIAN', 'bmkgsatu_dch'),
            self::makeSection('TAF', 'bmkgsatu_taf')
        ];
    }

    private static function makeSection(string $title, string $prefix)
    {
        return Section::make($title)
            ->columns(2)
            ->collapsible()
            ->schema([
                self::makeField("$prefix"."_creation_mode", 'Mode Pembuatan', 'toggle'),
                self::makeField("$prefix"."_creation_mode_offset", 'Offset (menit)', 'input'),

                self::makeField("$prefix"."_sent_mode", 'Mode Pengiriman', 'toggle'),
                self::makeField("$prefix"."_sent_mode_offset", 'Offset (menit)', 'input'),
            ]);
    }

    private static function makeField(string $name, string $label, string $type)
    {
        if ($type === 'toggle') {
            return ToggleButtons::make($name)
                ->label($label)
                ->options([true => 'Auto', false => 'Manual'])
                ->colors([true => 'info', false => 'warning'])
                ->grouped();
        }
       
        return TextInput::make($name)
            ->label($label)
            ->numeric()
            ->maxValue(5)
            ->minValue(-10);
    }
}

<?php

namespace App\Filament\Components;

use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class DataTabsFilter
{
    /**
     * @param string $modelClass Kelas model Eloquent, misalnya \App\Models\DataAws::class
     * @param string $timestampColumn Nama kolom timestamp, misalnya 'data_timestamp'
     */
    public static function make(string $modelClass, string $timestampColumn = 'data_timestamp'): array
    {
        $offset = '+0'; # UTC

        return [
            'all' => Tab::make('Semua'),

            'hour_0' => Tab::make('Jam Sekarang')
                ->badge(function () use ($modelClass, $timestampColumn, $offset) {
                    $now = Carbon::now($offset);
                    return $modelClass::whereDate($timestampColumn, $now->toDateString())
                        ->whereRaw("HOUR($timestampColumn) = ?", [$now->hour])
                        ->count();
                })
                ->modifyQueryUsing(function (Builder $query) use ($timestampColumn, $offset) {
                    $now = Carbon::now($offset);
                    return $query->whereDate($timestampColumn, $now->toDateString())
                        ->whereRaw("HOUR($timestampColumn) = ?", [$now->hour]);
                }),

            'hour_1' => Tab::make('1 Jam Lalu')
                ->badge(function () use ($modelClass, $timestampColumn, $offset) {
                    $time = Carbon::now($offset)->subHour();
                    return $modelClass::whereDate($timestampColumn, $time->toDateString())
                        ->whereRaw("HOUR($timestampColumn) = ?", [$time->hour])
                        ->count();
                })
                ->modifyQueryUsing(function (Builder $query) use ($timestampColumn, $offset) {
                    $time = Carbon::now($offset)->subHour();
                    return $query->whereDate($timestampColumn, $time->toDateString())
                        ->whereRaw("HOUR($timestampColumn) = ?", [$time->hour]);
                }),

            'hour_2' => Tab::make('2 Jam Lalu')
                ->badge(function () use ($modelClass, $timestampColumn, $offset) {
                    $time = Carbon::now($offset)->subHours(2);
                    return $modelClass::whereDate($timestampColumn, $time->toDateString())
                        ->whereRaw("HOUR($timestampColumn) = ?", [$time->hour])
                        ->count();
                })
                ->modifyQueryUsing(function (Builder $query) use ($timestampColumn, $offset) {
                    $time = Carbon::now($offset)->subHours(2);
                    return $query->whereDate($timestampColumn, $time->toDateString())
                        ->whereRaw("HOUR($timestampColumn) = ?", [$time->hour]);
                }),

            'hour_3' => Tab::make('3 Jam Lalu')
                ->badge(function () use ($modelClass, $timestampColumn, $offset) {
                    $time = Carbon::now($offset)->subHours(3);
                    return $modelClass::whereDate($timestampColumn, $time->toDateString())
                        ->whereRaw("HOUR($timestampColumn) = ?", [$time->hour])
                        ->count();
                })
                ->modifyQueryUsing(function (Builder $query) use ($timestampColumn, $offset) {
                    $time = Carbon::now($offset)->subHours(3);
                    return $query->whereDate($timestampColumn, $time->toDateString())
                        ->whereRaw("HOUR($timestampColumn) = ?", [$time->hour]);
                }),

            'today' => Tab::make('Hari Ini')
                ->badge(fn() => $modelClass::whereDate($timestampColumn, Carbon::today($offset))->count())
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereDate($timestampColumn, Carbon::today($offset))
                ),

            'yesterday' => Tab::make('Kemarin')
                ->badge(fn() => $modelClass::whereDate($timestampColumn, Carbon::yesterday($offset))->count())
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereDate($timestampColumn, Carbon::yesterday($offset))
                ),

            'last_7_days' => Tab::make('7 Hari Terakhir')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereBetween($timestampColumn, [
                        Carbon::now($offset)->subDays(7),
                        Carbon::now($offset),
                    ])
                ),

            'last_30_days' => Tab::make('30 Hari Terakhir')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereBetween($timestampColumn, [
                        Carbon::now($offset)->subDays(30),
                        Carbon::now($offset),
                    ])
                ),

            'this_month' => Tab::make('Bulan Ini')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereMonth($timestampColumn, Carbon::now($offset)->month)
                        ->whereYear($timestampColumn, Carbon::now($offset)->year)
                ),
        ];
    }
}

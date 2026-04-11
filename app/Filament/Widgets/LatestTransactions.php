<?php

namespace App\Filament\Widgets;

use App\Models\Income;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestTransactions extends BaseWidget
{
    protected static ?string $heading = '🕐 Transaksi Terbaru';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Income::query()->latest()->limit(10))
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#'),
                Tables\Columns\TextColumn::make('tanggal')->date('d/m/Y'),
                Tables\Columns\TextColumn::make('aplikasi')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains(strtolower($state), 'capcut') => 'info',
                        str_contains(strtolower($state), 'canva') => 'success',
                        str_contains(strtolower($state), 'spotify') => 'warning',
                        str_contains(strtolower($state), 'netflix') => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('jenis'),
                Tables\Columns\TextColumn::make('laba')
                    ->money('IDR', locale: 'id'),
                Tables\Columns\TextColumn::make('source')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'telegram' => 'info',
                        'whatsapp' => 'success',
                        default => 'gray',
                    }),
            ])
            ->paginated(false);
    }
}

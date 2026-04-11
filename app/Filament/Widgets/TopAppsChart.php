<?php

namespace App\Filament\Widgets;

use App\Models\Income;
use Filament\Widgets\ChartWidget;

class TopAppsChart extends ChartWidget
{
    protected static ?string $heading = '🏆 Top 5 Aplikasi Terlaris';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $stats = Income::selectRaw('aplikasi, SUM(laba) as total, COUNT(*) as count')
            ->groupBy('aplikasi')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $colors = [
            'rgba(59, 130, 246, 0.8)',  // blue
            'rgba(34, 197, 94, 0.8)',   // green
            'rgba(249, 115, 22, 0.8)',  // orange
            'rgba(239, 68, 68, 0.8)',   // red
            'rgba(168, 85, 247, 0.8)',  // purple
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Total Laba (Rp)',
                    'data' => $stats->pluck('total')->toArray(),
                    'backgroundColor' => array_slice($colors, 0, $stats->count()),
                ],
            ],
            'labels' => $stats->pluck('aplikasi')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}

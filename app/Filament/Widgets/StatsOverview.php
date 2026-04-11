<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Models\Income;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $incomeToday = Income::whereDate('tanggal', $today)->sum('laba');
        $incomeMonth = Income::whereBetween('tanggal', [$monthStart, $monthEnd])->sum('laba');
        $expenseMonth = Expense::whereBetween('tanggal', [$monthStart, $monthEnd])->sum('nominal');
        $nettProfit = $incomeMonth - $expenseMonth;
        $txToday = Income::whereDate('tanggal', $today)->count();

        // Last 7 days income for chart
        $last7 = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $last7[] = (int) Income::whereDate('tanggal', $date)->sum('laba');
        }

        return [
            Stat::make('💰 Pemasukan Hari Ini', 'Rp ' . number_format($incomeToday, 0, ',', '.'))
                ->description("{$txToday} transaksi")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($last7)
                ->color('success'),

            Stat::make('📊 Pemasukan Bulan Ini', 'Rp ' . number_format($incomeMonth, 0, ',', '.'))
                ->description(Carbon::now()->translatedFormat('F Y'))
                ->color('info'),

            Stat::make('💸 Pengeluaran Bulan Ini', 'Rp ' . number_format($expenseMonth, 0, ',', '.'))
                ->description(Carbon::now()->translatedFormat('F Y'))
                ->color('danger'),

            Stat::make('📈 Nett Profit', 'Rp ' . number_format($nettProfit, 0, ',', '.'))
                ->description($nettProfit >= 0 ? 'Profit ✅' : 'Rugi ❌')
                ->color($nettProfit >= 0 ? 'success' : 'danger'),
        ];
    }
}

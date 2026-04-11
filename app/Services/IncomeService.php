<?php

namespace App\Services;

use App\Models\Income;
use Carbon\Carbon;

class IncomeService
{
    /**
     * Format angka ke IDR
     */
    public function formatIDR($n): string
    {
        return 'Rp ' . number_format($n, 0, ',', '.');
    }

    /**
     * Tambah transaksi pemasukan
     */
    public function add(array $data): array
    {
        $income = Income::create([
            'tanggal' => $data['tanggal'] ?? Carbon::today(),
            'aplikasi' => $data['aplikasi'],
            'jenis' => $data['jenis'],
            'laba' => $data['laba'],
            'source' => $data['source'] ?? 'dashboard',
            'source_user' => $data['source_user'] ?? null,
        ]);

        $tanggal = Carbon::parse($income->tanggal)->format('d/m/Y');
        $message = "✅ Tercatat #{$income->id}\n{$tanggal}\n{$income->aplikasi} | {$income->jenis} | {$this->formatIDR($income->laba)}";

        return ['income' => $income, 'message' => $message];
    }

    /**
     * Ambil transaksi hari ini
     */
    public function today(): array
    {
        $today = Carbon::today();
        $incomes = Income::whereDate('tanggal', $today)->orderBy('id')->get();

        if ($incomes->isEmpty()) {
            return ['incomes' => [], 'message' => "Belum ada transaksi hari ini ({$today->format('d/m/Y')})."];
        }

        $total = 0;
        $lines = [];
        foreach ($incomes as $income) {
            $total += $income->laba;
            $lines[] = "#{$income->id} {$income->aplikasi} | {$income->jenis} | {$this->formatIDR($income->laba)}";
        }

        $message = "📌 Hari ini ({$today->format('d/m/Y')})\n" . implode("\n", $lines) . "\n\nTotal: {$this->formatIDR($total)}";

        return ['incomes' => $incomes, 'total' => $total, 'message' => $message];
    }

    /**
     * Ambil transaksi kemarin
     */
    public function yesterday(): array
    {
        $yesterday = Carbon::yesterday();
        $incomes = Income::whereDate('tanggal', $yesterday)->orderBy('id')->get();

        if ($incomes->isEmpty()) {
            return ['incomes' => [], 'message' => "Belum ada transaksi kemarin ({$yesterday->format('d/m/Y')})."];
        }

        $total = 0;
        $lines = [];
        foreach ($incomes as $income) {
            $total += $income->laba;
            $lines[] = "#{$income->id} {$income->aplikasi} | {$income->jenis} | {$this->formatIDR($income->laba)}";
        }

        $message = "📌 Kemarin ({$yesterday->format('d/m/Y')})\n" . implode("\n", $lines) . "\n\nTotal: {$this->formatIDR($total)}";

        return ['incomes' => $incomes, 'total' => $total, 'message' => $message];
    }

    /**
     * Ambil transaksi minggu ini (Senin-Minggu)
     */
    public function week(): array
    {
        $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $weekEnd = Carbon::now()->endOfWeek(Carbon::SUNDAY);

        $incomes = Income::whereBetween('tanggal', [$weekStart, $weekEnd])->orderBy('id')->get();

        if ($incomes->isEmpty()) {
            return ['incomes' => [], 'message' => "Belum ada transaksi minggu ini."];
        }

        $total = 0;
        $lines = [];
        $dailyTotals = [];

        foreach ($incomes as $income) {
            $total += $income->laba;
            $tgl = Carbon::parse($income->tanggal)->format('d/m/Y');
            $lines[] = "#{$income->id} {$tgl} | {$income->aplikasi} | {$income->jenis} | {$this->formatIDR($income->laba)}";

            if (!isset($dailyTotals[$tgl])) $dailyTotals[$tgl] = 0;
            $dailyTotals[$tgl] += $income->laba;
        }

        $daysActive = count($dailyTotals);
        $avg = $daysActive > 0 ? round($total / $daysActive) : 0;

        $message = "📅 Minggu Ini ({$weekStart->format('d/m/Y')} - {$weekEnd->format('d/m/Y')})\n"
            . implode("\n", $lines)
            . "\n\n━━━━━━━━━━━━━━━\nTotal: {$this->formatIDR($total)}\nTransaksi: " . count($incomes) . "x\nRata-rata/hari: {$this->formatIDR($avg)}";

        return ['incomes' => $incomes, 'total' => $total, 'message' => $message];
    }

    /**
     * Ambil transaksi bulan ini
     */
    public function month(): array
    {
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $incomes = Income::whereBetween('tanggal', [$monthStart, $monthEnd])->orderBy('id')->get();

        if ($incomes->isEmpty()) {
            $bulan = Carbon::now()->translatedFormat('F Y');
            return ['incomes' => [], 'message' => "Belum ada transaksi bulan {$bulan}."];
        }

        $total = 0;
        $lines = [];
        $dailyTotals = [];

        foreach ($incomes as $income) {
            $total += $income->laba;
            $tgl = Carbon::parse($income->tanggal)->format('d/m/Y');
            $lines[] = "#{$income->id} {$tgl} | {$income->aplikasi} | {$income->jenis} | {$this->formatIDR($income->laba)}";

            if (!isset($dailyTotals[$tgl])) $dailyTotals[$tgl] = 0;
            $dailyTotals[$tgl] += $income->laba;
        }

        $daysActive = count($dailyTotals);
        $avg = $daysActive > 0 ? round($total / $daysActive) : 0;
        $bulan = Carbon::now()->translatedFormat('F Y');

        $message = "📅 {$bulan}\n"
            . implode("\n", $lines)
            . "\n\n━━━━━━━━━━━━━━━\nTotal: {$this->formatIDR($total)}\nTransaksi: " . count($incomes) . "x\nHari aktif: {$daysActive} hari\nRata-rata/hari: {$this->formatIDR($avg)}";

        return ['incomes' => $incomes, 'total' => $total, 'message' => $message];
    }

    /**
     * Semua transaksi
     */
    public function list(): array
    {
        $incomes = Income::orderBy('id')->get();

        if ($incomes->isEmpty()) {
            return ['incomes' => [], 'message' => "Belum ada transaksi."];
        }

        $total = 0;
        $lines = [];
        foreach ($incomes as $income) {
            $total += $income->laba;
            $tgl = Carbon::parse($income->tanggal)->format('d/m/Y');
            $lines[] = "#{$income->id} {$tgl} | {$income->aplikasi} | {$income->jenis} | {$this->formatIDR($income->laba)}";
        }

        $message = "📋 Semua Transaksi\n" . implode("\n", $lines) . "\n\nTotal: {$this->formatIDR($total)}";

        return ['incomes' => $incomes, 'total' => $total, 'message' => $message];
    }

    /**
     * Ringkasan per aplikasi
     */
    public function summary(): array
    {
        $incomes = Income::orderBy('id')->get();

        if ($incomes->isEmpty()) {
            return ['summary' => [], 'message' => "Belum ada transaksi."];
        }

        $appStats = [];
        foreach ($incomes as $income) {
            $app = $income->aplikasi;
            if (!isset($appStats[$app])) {
                $appStats[$app] = ['count' => 0, 'total' => 0];
            }
            $appStats[$app]['count']++;
            $appStats[$app]['total'] += $income->laba;
        }

        uasort($appStats, fn($a, $b) => $b['total'] <=> $a['total']);

        $lines = [];
        $grandTotal = 0;
        foreach ($appStats as $app => $stats) {
            $lines[] = "{$app}: {$stats['count']}x transaksi\nTotal: {$this->formatIDR($stats['total'])}";
            $grandTotal += $stats['total'];
        }

        $message = "📊 Ringkasan per Aplikasi\n\n" . implode("\n\n", $lines) . "\n\n━━━━━━━━━━━━━━━\nGrand Total: {$this->formatIDR($grandTotal)}";

        return ['summary' => $appStats, 'grand_total' => $grandTotal, 'message' => $message];
    }

    /**
     * Top 5 aplikasi terlaris
     */
    public function top(): array
    {
        $incomes = Income::orderBy('id')->get();

        if ($incomes->isEmpty()) {
            return ['top' => [], 'message' => "Belum ada transaksi."];
        }

        $appStats = [];
        foreach ($incomes as $income) {
            $app = $income->aplikasi;
            if (!isset($appStats[$app])) {
                $appStats[$app] = ['count' => 0, 'total' => 0];
            }
            $appStats[$app]['count']++;
            $appStats[$app]['total'] += $income->laba;
        }

        uasort($appStats, fn($a, $b) => $b['total'] <=> $a['total']);
        $top5 = array_slice($appStats, 0, 5, true);

        $lines = [];
        $grandTotal = 0;
        $index = 0;
        $medals = ['🥇', '🥈', '🥉'];

        foreach ($top5 as $app => $stats) {
            $medal = $medals[$index] ?? ($index + 1) . '.';
            $lines[] = "{$medal} {$app}\n   {$stats['count']}x transaksi | {$this->formatIDR($stats['total'])}";
            $grandTotal += $stats['total'];
            $index++;
        }

        $count = count($top5);
        $message = "🏆 Top {$count} Aplikasi Terlaris\n\n" . implode("\n\n", $lines) . "\n\n━━━━━━━━━━━━━━━\nTotal dari Top {$count}: {$this->formatIDR($grandTotal)}";

        return ['top' => $top5, 'grand_total' => $grandTotal, 'message' => $message];
    }

    /**
     * Statistik lengkap
     */
    public function stats(): array
    {
        $incomes = Income::orderBy('id')->get();

        if ($incomes->isEmpty()) {
            return ['stats' => [], 'message' => "Belum ada transaksi."];
        }

        $totalLaba = 0;
        $maxLaba = 0;
        $minLaba = PHP_INT_MAX;
        $maxEntry = null;
        $minEntry = null;
        $appStats = [];
        $dailyStats = [];
        $jenisStats = [];

        foreach ($incomes as $income) {
            $totalLaba += $income->laba;
            $tgl = Carbon::parse($income->tanggal)->format('d/m/Y');

            if ($income->laba > $maxLaba) {
                $maxLaba = $income->laba;
                $maxEntry = $income;
            }
            if ($income->laba < $minLaba) {
                $minLaba = $income->laba;
                $minEntry = $income;
            }

            // Group by app
            if (!isset($appStats[$income->aplikasi])) $appStats[$income->aplikasi] = ['count' => 0, 'total' => 0];
            $appStats[$income->aplikasi]['count']++;
            $appStats[$income->aplikasi]['total'] += $income->laba;

            // Group by day
            if (!isset($dailyStats[$tgl])) $dailyStats[$tgl] = ['count' => 0, 'total' => 0];
            $dailyStats[$tgl]['count']++;
            $dailyStats[$tgl]['total'] += $income->laba;

            // Group by jenis
            if (!isset($jenisStats[$income->jenis])) $jenisStats[$income->jenis] = ['count' => 0, 'total' => 0];
            $jenisStats[$income->jenis]['count']++;
            $jenisStats[$income->jenis]['total'] += $income->laba;
        }

        $totalTransaksi = count($incomes);
        $totalDays = count($dailyStats);
        $avgPerTransaksi = round($totalLaba / $totalTransaksi);
        $avgPerDay = $totalDays > 0 ? round($totalLaba / $totalDays) : 0;

        // Most productive day
        uasort($dailyStats, fn($a, $b) => $b['total'] <=> $a['total']);
        $bestDay = array_key_first($dailyStats);
        $bestDayStats = $dailyStats[$bestDay];

        // Most popular app
        uasort($appStats, fn($a, $b) => $b['count'] <=> $a['count']);
        $topApp = array_key_first($appStats);
        $topAppStats = $appStats[$topApp];

        // Most popular jenis
        uasort($jenisStats, fn($a, $b) => $b['count'] <=> $a['count']);
        $topJenis = array_key_first($jenisStats);
        $topJenisStats = $jenisStats[$topJenis];

        $maxTgl = Carbon::parse($maxEntry->tanggal)->format('d/m/Y');
        $minTgl = Carbon::parse($minEntry->tanggal)->format('d/m/Y');

        $message = "📊 Statistik Lengkap\n\n"
            . "📈 RINGKASAN UMUM\n"
            . "Total Transaksi: {$totalTransaksi}x\n"
            . "Total Laba: {$this->formatIDR($totalLaba)}\n"
            . "Hari Aktif: {$totalDays} hari\n\n"
            . "💰 RATA-RATA\n"
            . "Per Transaksi: {$this->formatIDR($avgPerTransaksi)}\n"
            . "Per Hari: {$this->formatIDR($avgPerDay)}\n\n"
            . "🎯 REKOR\n"
            . "Transaksi Tertinggi:\n"
            . "  #{$maxEntry->id} {$this->formatIDR($maxLaba)}\n"
            . "  {$maxEntry->aplikasi} | {$maxEntry->jenis}\n"
            . "  {$maxTgl}\n\n"
            . "Transaksi Terendah:\n"
            . "  #{$minEntry->id} {$this->formatIDR($minLaba)}\n"
            . "  {$minEntry->aplikasi} | {$minEntry->jenis}\n"
            . "  {$minTgl}\n\n"
            . "🏅 PALING POPULER\n"
            . "Aplikasi: {$topApp}\n"
            . "  {$topAppStats['count']}x | {$this->formatIDR($topAppStats['total'])}\n\n"
            . "Jenis: {$topJenis}\n"
            . "  {$topJenisStats['count']}x | {$this->formatIDR($topJenisStats['total'])}\n\n"
            . "📅 HARI TERBAIK\n"
            . "{$bestDay}\n"
            . "  {$bestDayStats['count']}x transaksi | {$this->formatIDR($bestDayStats['total'])}";

        return [
            'stats' => [
                'total_transaksi' => $totalTransaksi,
                'total_laba' => $totalLaba,
                'hari_aktif' => $totalDays,
                'avg_per_transaksi' => $avgPerTransaksi,
                'avg_per_hari' => $avgPerDay,
            ],
            'message' => $message,
        ];
    }
}

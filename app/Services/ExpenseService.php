<?php

namespace App\Services;

use App\Models\Expense;
use Carbon\Carbon;

class ExpenseService
{
    public function formatIDR($n): string
    {
        return 'Rp ' . number_format($n, 0, ',', '.');
    }

    /**
     * Tambah pengeluaran
     */
    public function add(array $data): array
    {
        $expense = Expense::create([
            'tanggal' => $data['tanggal'] ?? Carbon::today(),
            'kategori' => $data['kategori'],
            'keterangan' => $data['keterangan'],
            'nominal' => $data['nominal'],
            'source' => $data['source'] ?? 'dashboard',
            'source_user' => $data['source_user'] ?? null,
        ]);

        $tanggal = Carbon::parse($expense->tanggal)->format('d/m/Y');
        $message = "✅ Pengeluaran dicatat #{$expense->id}\n📅 {$tanggal}\n🏷️ {$expense->kategori} | {$expense->keterangan}\n💸 {$this->formatIDR($expense->nominal)}";

        return ['expense' => $expense, 'message' => $message];
    }

    /**
     * Semua pengeluaran
     */
    public function list(): array
    {
        $expenses = Expense::orderBy('id')->get();

        if ($expenses->isEmpty()) {
            return ['expenses' => [], 'message' => "💸 Belum ada data pengeluaran."];
        }

        $total = 0;
        $lines = [];
        $no = 1;
        foreach ($expenses as $expense) {
            $total += $expense->nominal;
            $tgl = Carbon::parse($expense->tanggal)->format('d/m/Y');
            $lines[] = "{$no}. {$tgl} | {$expense->kategori} | {$expense->keterangan} | {$this->formatIDR($expense->nominal)} (ID: {$expense->id})";
            $no++;
        }

        $message = "💸 Semua Pengeluaran\n" . implode("\n", $lines) . "\n\nTotal: {$this->formatIDR($total)}";

        return ['expenses' => $expenses, 'total' => $total, 'message' => $message];
    }

    /**
     * Pengeluaran hari ini
     */
    public function today(): array
    {
        $today = Carbon::today();
        $expenses = Expense::whereDate('tanggal', $today)->orderBy('id')->get();

        if ($expenses->isEmpty()) {
            return ['expenses' => [], 'message' => "💸 Belum ada pengeluaran hari ini ({$today->format('d/m/Y')})."];
        }

        $total = 0;
        $lines = [];
        $kategoriTotals = [];
        $no = 1;

        foreach ($expenses as $expense) {
            $total += $expense->nominal;
            $lines[] = "{$no}. {$expense->kategori} | {$expense->keterangan} | {$this->formatIDR($expense->nominal)} (ID: {$expense->id})";
            $no++;

            if (!isset($kategoriTotals[$expense->kategori])) $kategoriTotals[$expense->kategori] = 0;
            $kategoriTotals[$expense->kategori] += $expense->nominal;
        }

        arsort($kategoriTotals);
        $kategoriLines = [];
        foreach ($kategoriTotals as $k => $v) {
            $kategoriLines[] = "  • {$k}: {$this->formatIDR($v)}";
        }

        $message = "💸 Pengeluaran Hari Ini ({$today->format('d/m/Y')})\n"
            . implode("\n", $lines)
            . "\n\n━━━━━━━━━━━━━━━\nTotal: {$this->formatIDR($total)}\n\n📊 Per Kategori:\n"
            . implode("\n", $kategoriLines);

        return ['expenses' => $expenses, 'total' => $total, 'message' => $message];
    }

    /**
     * Pengeluaran bulan ini
     */
    public function month(): array
    {
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $expenses = Expense::whereBetween('tanggal', [$monthStart, $monthEnd])->orderBy('id')->get();

        if ($expenses->isEmpty()) {
            $bulan = Carbon::now()->translatedFormat('F Y');
            return ['expenses' => [], 'message' => "💸 Belum ada pengeluaran bulan {$bulan}."];
        }

        $total = 0;
        $lines = [];
        $kategoriTotals = [];
        $no = 1;

        foreach ($expenses as $expense) {
            $total += $expense->nominal;
            $tgl = Carbon::parse($expense->tanggal)->format('d/m/Y');
            $lines[] = "{$no}. {$tgl} | {$expense->kategori} | {$expense->keterangan} | {$this->formatIDR($expense->nominal)} (ID: {$expense->id})";
            $no++;

            if (!isset($kategoriTotals[$expense->kategori])) $kategoriTotals[$expense->kategori] = 0;
            $kategoriTotals[$expense->kategori] += $expense->nominal;
        }

        arsort($kategoriTotals);
        $kategoriLines = [];
        foreach ($kategoriTotals as $k => $v) {
            $kategoriLines[] = "  • {$k}: {$this->formatIDR($v)}";
        }

        $bulan = Carbon::now()->translatedFormat('F Y');
        $message = "💸 Pengeluaran {$bulan}\n"
            . implode("\n", $lines)
            . "\n\n━━━━━━━━━━━━━━━\nTotal: {$this->formatIDR($total)}\nTransaksi: " . count($expenses) . "x\n\n📊 Per Kategori:\n"
            . implode("\n", $kategoriLines);

        return ['expenses' => $expenses, 'total' => $total, 'message' => $message];
    }
}

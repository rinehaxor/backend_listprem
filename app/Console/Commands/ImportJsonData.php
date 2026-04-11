<?php

namespace App\Console\Commands;

use App\Models\Email;
use App\Models\Expense;
use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportJsonData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:json {type : Tipe data: incomes, expenses, atau emails} {file : Path ke file JSON}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data dari file JSON (export dari Spreadsheet/bot lama) ke database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type');
        $file = $this->argument('file');

        if (!in_array($type, ['incomes', 'expenses', 'emails'])) {
            $this->error('Tipe tidak valid! Harus salah satu dari: incomes, expenses, emails');
            return;
        }

        if (!File::exists($file)) {
            $this->error("File {$file} tidak ditemukan!");
            return;
        }

        $json = File::get($file);
        $data = json_decode($json, true);

        if (!$data || !is_array($data)) {
            $this->error('File JSON tidak valid atau kosong.');
            return;
        }

        $this->info("Ditemukan " . count($data) . " baris data. Memulai import untuk tabel {$type}...");

        $success = 0;
        $failed = 0;

        foreach ($data as $index => $row) {
            try {
                if ($type === 'incomes') {
                    Income::create([
                        'tanggal' => isset($row['tanggal']) ? Carbon::parse($row['tanggal']) : Carbon::today(),
                        'aplikasi' => $row['aplikasi'] ?? 'Unknown',
                        'jenis' => $row['jenis'] ?? '-',
                        'laba' => $this->parseIDR($row['laba']),
                        'source' => $row['source'] ?? 'telegram',
                        'source_user' => $row['source_user'] ?? 'Imported',
                        'created_at' => isset($row['tanggal']) ? Carbon::parse($row['tanggal']) : now(),
                    ]);
                } elseif ($type === 'expenses') {
                    Expense::create([
                        'tanggal' => isset($row['tanggal']) ? Carbon::parse($row['tanggal']) : Carbon::today(),
                        'kategori' => $row['kategori'] ?? 'Lainnya',
                        'keterangan' => $row['keterangan'] ?? '-',
                        'nominal' => $this->parseIDR($row['nominal'] ?? $row['laba'] ?? 0),
                        'source' => $row['source'] ?? 'telegram',
                        'source_user' => $row['source_user'] ?? 'Imported',
                        'created_at' => isset($row['tanggal']) ? Carbon::parse($row['tanggal']) : now(),
                    ]);
                } elseif ($type === 'emails') {
                    Email::create([
                        'akun' => $row['akun'] ?? $row['email'] ?? 'Unknown',
                        'password' => $row['password'] ?? '',
                        'keterangan' => $row['keterangan'] ?? '-',
                        'source' => $row['source'] ?? 'telegram',
                        'source_user' => $row['source_user'] ?? 'Imported',
                    ]);
                }
                $success++;
            } catch (\Exception $e) {
                $this->error("Gagal import baris ke-" . ($index + 1) . ": " . $e->getMessage());
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Import selesai!");
        $this->info("Berhasil: {$success} baris");
        if ($failed > 0) {
            $this->error("Gagal: {$failed} baris");
        }
    }

    /**
     * Parsing format angka IDR ke integer murni.
     * Cth: "Rp 15.000" -> 15000, "15000" -> 15000
     */
    private function parseIDR($val): int
    {
        if (is_numeric($val)) return (int) $val;
        $cleaned = preg_replace('/[^0-9]/', '', (string)$val);
        return (int) $cleaned;
    }
}

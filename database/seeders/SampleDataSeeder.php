<?php

namespace Database\Seeders;

use App\Models\ApiKey;
use App\Models\Email;
use App\Models\Expense;
use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // ===== API KEYS =====
        $telegramKey = ApiKey::create([
            'name' => 'Telegram Bot',
            'key' => 'tg_' . str_repeat('a', 61), // tg_aaa...a (64 chars)
            'platform' => 'telegram',
            'is_active' => true,
        ]);

        $waKey = ApiKey::create([
            'name' => 'WhatsApp Bot',
            'key' => 'wa_' . str_repeat('b', 61),
            'platform' => 'whatsapp',
            'is_active' => true,
        ]);

        $this->command->info("📌 API Keys created:");
        $this->command->info("   Telegram: tg_" . str_repeat('a', 61));
        $this->command->info("   WhatsApp: wa_" . str_repeat('b', 61));

        // ===== SAMPLE INCOMES =====
        $incomes = [
            ['aplikasi' => 'CapCut', 'jenis' => '1 bulan', 'laba' => 8000, 'days_ago' => 0],
            ['aplikasi' => 'Canva', 'jenis' => 'lifetime', 'laba' => 15000, 'days_ago' => 0],
            ['aplikasi' => 'Spotify', 'jenis' => '1 bulan', 'laba' => 5000, 'days_ago' => 0],
            ['aplikasi' => 'CapCut', 'jenis' => '1 bulan', 'laba' => 8000, 'days_ago' => 1],
            ['aplikasi' => 'Canva', 'jenis' => '1 bulan', 'laba' => 10000, 'days_ago' => 1],
            ['aplikasi' => 'Netflix', 'jenis' => '1 bulan', 'laba' => 12000, 'days_ago' => 1],
            ['aplikasi' => 'CapCut', 'jenis' => 'lifetime', 'laba' => 20000, 'days_ago' => 2],
            ['aplikasi' => 'Spotify', 'jenis' => '3 bulan', 'laba' => 12000, 'days_ago' => 2],
            ['aplikasi' => 'Alight Motion', 'jenis' => '1 bulan', 'laba' => 6000, 'days_ago' => 3],
            ['aplikasi' => 'CapCut', 'jenis' => '1 bulan', 'laba' => 8000, 'days_ago' => 3],
            ['aplikasi' => 'Canva', 'jenis' => 'lifetime', 'laba' => 15000, 'days_ago' => 4],
            ['aplikasi' => 'Netflix', 'jenis' => '1 bulan', 'laba' => 12000, 'days_ago' => 5],
            ['aplikasi' => 'CapCut', 'jenis' => '1 bulan', 'laba' => 8000, 'days_ago' => 5],
            ['aplikasi' => 'Spotify', 'jenis' => '1 bulan', 'laba' => 5000, 'days_ago' => 6],
            ['aplikasi' => 'YouTube Premium', 'jenis' => '1 bulan', 'laba' => 10000, 'days_ago' => 7],
        ];

        foreach ($incomes as $data) {
            Income::create([
                'tanggal' => Carbon::today()->subDays($data['days_ago']),
                'aplikasi' => $data['aplikasi'],
                'jenis' => $data['jenis'],
                'laba' => $data['laba'],
                'source' => 'dashboard',
                'source_user' => 'Seeder',
            ]);
        }
        $this->command->info("✅ " . count($incomes) . " sample incomes created.");

        // ===== SAMPLE EXPENSES =====
        $expenses = [
            ['kategori' => 'Akun', 'keterangan' => 'Beli akun Netflix bulk', 'nominal' => 50000, 'days_ago' => 0],
            ['kategori' => 'Makan', 'keterangan' => 'Beli nasi padang', 'nominal' => 15000, 'days_ago' => 0],
            ['kategori' => 'Akun', 'keterangan' => 'Beli akun Spotify', 'nominal' => 30000, 'days_ago' => 1],
            ['kategori' => 'Transport', 'keterangan' => 'Naik ojol', 'nominal' => 20000, 'days_ago' => 2],
            ['kategori' => 'Akun', 'keterangan' => 'Beli akun Canva', 'nominal' => 25000, 'days_ago' => 3],
            ['kategori' => 'Tagihan', 'keterangan' => 'Bayar internet', 'nominal' => 300000, 'days_ago' => 5],
        ];

        foreach ($expenses as $data) {
            Expense::create([
                'tanggal' => Carbon::today()->subDays($data['days_ago']),
                'kategori' => $data['kategori'],
                'keterangan' => $data['keterangan'],
                'nominal' => $data['nominal'],
                'source' => 'dashboard',
                'source_user' => 'Seeder',
            ]);
        }
        $this->command->info("✅ " . count($expenses) . " sample expenses created.");

        // ===== SAMPLE EMAILS =====
        $emails = [
            ['akun' => 'test1@gmail.com', 'password' => 'pass123', 'keterangan' => 'Email utama'],
            ['akun' => 'backup@gmail.com', 'password' => 'backup456', 'keterangan' => 'Email cadangan'],
            ['akun' => 'netflix@gmail.com', 'password' => 'nflx789', 'keterangan' => 'Akun Netflix'],
        ];

        foreach ($emails as $data) {
            Email::create([
                'akun' => $data['akun'],
                'password' => $data['password'],
                'keterangan' => $data['keterangan'],
                'source' => 'dashboard',
                'source_user' => 'Seeder',
            ]);
        }
        $this->command->info("✅ " . count($emails) . " sample emails created.");
    }
}

<?php

namespace App\Services;

use App\Models\XAccount;

class XAccountService
{
    /**
     * Tambah X Account
     */
    public function add(array $data): array
    {
        $xAccount = XAccount::create([
            'nama' => $data['nama'] ?? null,
            'username' => $data['username'],
            'email' => $data['email'],
            'status' => $data['status'],
            'link' => $data['link'],
            'source' => $data['source'] ?? 'dashboard',
            'source_user' => $data['source_user'] ?? null,
        ]);

        $message = "✅ Akun X dicatat #{$xAccount->id}\n📛 {$xAccount->nama}\n👤 {$xAccount->username}\n📧 {$xAccount->email}\n📌 {$xAccount->status}\n🔗 {$xAccount->link}";

        return ['x_account' => $xAccount, 'message' => $message];
    }

    /**
     * Semua X Account
     */
    public function list(): array
    {
        $xAccounts = XAccount::orderBy('id')->get();

        if ($xAccounts->isEmpty()) {
            return ['x_accounts' => [], 'message' => "❌ Belum ada data Akun X."];
        }

        $lines = [];
        $no = 1;
        foreach ($xAccounts as $x) {
            $lines[] = "{$no}. {$x->nama} | {$x->username} | {$x->email} | {$x->status} | {$x->link} (ID: {$x->id})";
            $no++;
        }

        $message = "🗂️ Semua Akun X\n" . implode("\n", $lines) . "\n\nTotal: " . count($xAccounts) . " akun";

        return ['x_accounts' => $xAccounts, 'message' => $message];
    }
}

<?php

namespace App\Services;

use App\Models\Email;

class EmailService
{
    /**
     * Tambah email
     */
    public function add(array $data): array
    {
        $email = Email::create([
            'akun' => $data['akun'],
            'password' => $data['password'],
            'keterangan' => $data['keterangan'],
            'source' => $data['source'] ?? 'dashboard',
            'source_user' => $data['source_user'] ?? null,
        ]);

        $message = "✅ Email dicatat #{$email->id}\n📧 {$email->akun}\n🔑 {$data['password']}\n📝 {$email->keterangan}";

        return ['email' => $email, 'message' => $message];
    }

    /**
     * Semua email
     */
    public function list(): array
    {
        $emails = Email::orderBy('id')->get();

        if ($emails->isEmpty()) {
            return ['emails' => [], 'message' => "📧 Belum ada data email."];
        }

        $lines = [];
        foreach ($emails as $email) {
            $lines[] = "#{$email->id} {$email->akun} | {$email->password} | {$email->keterangan}";
        }

        $message = "📧 Semua Email\n" . implode("\n", $lines) . "\n\nTotal: " . count($emails) . " email";

        return ['emails' => $emails, 'message' => $message];
    }
}

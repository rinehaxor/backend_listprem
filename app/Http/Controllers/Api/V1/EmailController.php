<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Email;
use App\Services\EmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function __construct(
        protected EmailService $service
    ) {}

    /**
     * POST /api/v1/emails — Tambah email
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'akun' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'keterangan' => 'required|string|max:255',
            'source_user' => 'nullable|string|max:255',
        ]);

        $result = $this->service->add([
            'akun' => $request->akun,
            'password' => $request->password,
            'keterangan' => $request->keterangan,
            'source' => $request->api_key_platform ?? 'dashboard',
            'source_user' => $request->source_user,
        ]);

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => $result['email'],
        ], 201);
    }

    /**
     * GET /api/v1/emails — Semua email
     */
    public function index(): JsonResponse
    {
        $result = $this->service->list();

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => $result['emails'],
        ]);
    }

    /**
     * PUT /api/v1/emails/{id} — Edit email
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $email = Email::findOrFail($id);

        $request->validate([
            'akun' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $changes = [];
        foreach (['akun', 'keterangan'] as $field) {
            if ($request->has($field)) {
                $old = $email->$field;
                $email->$field = $request->$field;
                $changes[] = "Field: " . ucfirst($field) . "\nDari: {$old}\nJadi: {$request->$field}";
            }
        }

        if ($request->has('password')) {
            $email->password = $request->password;
            $changes[] = "Field: Password\nDari: ****\nJadi: (updated)";
        }

        $email->save();

        $message = "✏️ Berhasil edit email #{$id}\n\n" . implode("\n\n", $changes);

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $email,
        ]);
    }

    /**
     * DELETE /api/v1/emails/{id} — Hapus email
     */
    public function destroy(int $id): JsonResponse
    {
        $email = Email::findOrFail($id);

        $message = "🗑️ Email #{$id} dihapus\n{$email->akun} | {$email->keterangan}";

        $email->delete();

        $remaining = Email::count();
        $message .= "\n\nSisa {$remaining} email";

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }
}

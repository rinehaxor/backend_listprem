<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\XAccount;
use App\Services\XAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class XAccountController extends Controller
{
    public function __construct(
        protected XAccountService $service
    ) {}

    /**
     * POST /api/v1/x-accounts — Tambah akun X
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nama' => 'nullable|string|max:255',
            'username' => 'required|string|max:255',
            'email' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'link' => 'required|string|max:255',
            'source_user' => 'nullable|string|max:255',
        ]);

        $result = $this->service->add([
            'nama' => $request->nama,
            'username' => $request->username,
            'email' => $request->email,
            'status' => $request->status,
            'link' => $request->link,
            'source' => $request->api_key_platform ?? 'dashboard',
            'source_user' => $request->source_user,
        ]);

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => $result['x_account'],
        ], 201);
    }

    /**
     * GET /api/v1/x-accounts — Semua akun X
     */
    public function index(): JsonResponse
    {
        $result = $this->service->list();

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => $result['x_accounts'],
        ]);
    }

    /**
     * PUT /api/v1/x-accounts/{id} — Edit akun X
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $xAccount = XAccount::findOrFail($id);

        $request->validate([
            'nama' => 'nullable|string|max:255',
            'username' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:255',
        ]);

        $changes = [];
        foreach (['nama', 'username', 'email', 'status', 'link'] as $field) {
            if ($request->has($field)) {
                $old = $xAccount->$field;
                $xAccount->$field = $request->$field;
                $changes[] = "Field: " . ucfirst($field) . "\nDari: {$old}\nJadi: {$request->$field}";
            }
        }

        $xAccount->save();

        $message = "✏️ Berhasil edit Akun X #{$id}\n\n" . implode("\n\n", $changes);

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $xAccount,
        ]);
    }

    /**
     * DELETE /api/v1/x-accounts/{id} — Hapus akun X
     */
    public function destroy(int $id): JsonResponse
    {
        $xAccount = XAccount::findOrFail($id);

        $message = "🗑️ Akun X #{$id} dihapus\n{$xAccount->username} | {$xAccount->email}";

        $xAccount->delete();

        $remaining = XAccount::count();
        $message .= "\n\nSisa {$remaining} akun X";

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }
}

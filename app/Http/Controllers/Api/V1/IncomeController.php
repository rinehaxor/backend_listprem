<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Income;
use App\Services\IncomeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    public function __construct(
        protected IncomeService $service
    ) {}

    /**
     * POST /api/v1/incomes — Tambah pemasukan
     * Body: { aplikasi, jenis, laba, source_user? }
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'aplikasi' => 'required|string|max:255',
            'jenis' => 'required|string|max:255',
            'laba' => 'required|numeric|min:1',
            'source_user' => 'nullable|string|max:255',
            'tanggal' => 'nullable|date',
        ]);

        $result = $this->service->add([
            'aplikasi' => $request->aplikasi,
            'jenis' => $request->jenis,
            'laba' => $request->laba,
            'tanggal' => $request->tanggal,
            'source' => $request->api_key_platform ?? 'dashboard',
            'source_user' => $request->source_user,
        ]);

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => $result['income'],
        ], 201);
    }

    /**
     * GET /api/v1/incomes — Semua transaksi
     */
    public function index(): JsonResponse
    {
        $result = $this->service->list();

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => $result['incomes'],
            'total' => $result['total'] ?? 0,
        ]);
    }

    /**
     * GET /api/v1/incomes/today
     */
    public function today(): JsonResponse
    {
        $result = $this->service->today();

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => $result['incomes'],
            'total' => $result['total'] ?? 0,
        ]);
    }

    /**
     * GET /api/v1/incomes/yesterday
     */
    public function yesterday(): JsonResponse
    {
        $result = $this->service->yesterday();

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => $result['incomes'],
            'total' => $result['total'] ?? 0,
        ]);
    }

    /**
     * GET /api/v1/incomes/week
     */
    public function week(): JsonResponse
    {
        $result = $this->service->week();

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => $result['incomes'],
            'total' => $result['total'] ?? 0,
        ]);
    }

    /**
     * GET /api/v1/incomes/month
     */
    public function month(): JsonResponse
    {
        $result = $this->service->month();

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => $result['incomes'],
            'total' => $result['total'] ?? 0,
        ]);
    }

    /**
     * GET /api/v1/incomes/summary
     */
    public function summary(): JsonResponse
    {
        $result = $this->service->summary();

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => $result['summary'] ?? [],
            'grand_total' => $result['grand_total'] ?? 0,
        ]);
    }

    /**
     * GET /api/v1/incomes/top
     */
    public function top(): JsonResponse
    {
        $result = $this->service->top();

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => $result['top'] ?? [],
            'grand_total' => $result['grand_total'] ?? 0,
        ]);
    }

    /**
     * GET /api/v1/incomes/stats
     */
    public function stats(): JsonResponse
    {
        $result = $this->service->stats();

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => $result['stats'] ?? [],
        ]);
    }

    /**
     * PUT /api/v1/incomes/{id} — Edit transaksi
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $income = Income::findOrFail($id);

        $request->validate([
            'aplikasi' => 'nullable|string|max:255',
            'jenis' => 'nullable|string|max:255',
            'laba' => 'nullable|numeric|min:1',
        ]);

        $changes = [];
        foreach (['aplikasi', 'jenis', 'laba'] as $field) {
            if ($request->has($field)) {
                $old = $income->$field;
                $income->$field = $request->$field;
                $displayOld = $field === 'laba' ? $this->service->formatIDR($old) : $old;
                $displayNew = $field === 'laba' ? $this->service->formatIDR($request->$field) : $request->$field;
                $changes[] = "Field: " . ucfirst($field) . "\nDari: {$displayOld}\nJadi: {$displayNew}";
            }
        }

        $income->save();

        $message = "✏️ Berhasil edit entry #{$id}\n\n" . implode("\n\n", $changes);

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $income,
        ]);
    }

    /**
     * DELETE /api/v1/incomes/{id} — Hapus transaksi
     */
    public function destroy(int $id): JsonResponse
    {
        $income = Income::findOrFail($id);

        $message = "🗑️ Berhasil dihapus #{$id}\n{$income->aplikasi} | {$income->jenis} | {$this->service->formatIDR($income->laba)}";

        $income->delete();

        $remaining = Income::count();
        $message .= "\n\nSisa {$remaining} entry";

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    /**
     * DELETE /api/v1/incomes/last — Undo (hapus terakhir)
     */
    public function destroyLast(): JsonResponse
    {
        $income = Income::latest()->first();

        if (!$income) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada entry yang bisa di-undo.',
            ], 404);
        }

        $message = "↩️ Undo sukses: hapus entry #{$income->id}\n{$income->aplikasi} | {$income->jenis} | {$this->service->formatIDR($income->laba)}";

        $income->delete();

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }
}

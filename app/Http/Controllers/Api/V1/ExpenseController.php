<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Services\ExpenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function __construct(
        protected ExpenseService $service
    ) {}

    /**
     * POST /api/v1/expenses — Tambah pengeluaran
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'kategori' => 'required|string|max:255',
            'keterangan' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:1',
            'source_user' => 'nullable|string|max:255',
            'tanggal' => 'nullable|date',
        ]);

        $result = $this->service->add([
            'kategori' => $request->kategori,
            'keterangan' => $request->keterangan,
            'nominal' => $request->nominal,
            'tanggal' => $request->tanggal,
            'source' => $request->api_key_platform ?? 'dashboard',
            'source_user' => $request->source_user,
        ]);

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => $result['expense'],
        ], 201);
    }

    /**
     * GET /api/v1/expenses — Semua pengeluaran
     */
    public function index(): JsonResponse
    {
        $result = $this->service->list();

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => $result['expenses'],
            'total' => $result['total'] ?? 0,
        ]);
    }

    /**
     * GET /api/v1/expenses/today
     */
    public function today(): JsonResponse
    {
        $result = $this->service->today();

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => $result['expenses'],
            'total' => $result['total'] ?? 0,
        ]);
    }

    /**
     * GET /api/v1/expenses/month
     */
    public function month(): JsonResponse
    {
        $result = $this->service->month();

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => $result['expenses'],
            'total' => $result['total'] ?? 0,
        ]);
    }

    /**
     * DELETE /api/v1/expenses/{id} — Hapus pengeluaran
     */
    public function destroy(int $id): JsonResponse
    {
        $expense = Expense::findOrFail($id);

        $message = "🗑️ Pengeluaran #{$id} dihapus\n{$expense->kategori} | {$expense->keterangan} | {$this->service->formatIDR($expense->nominal)}";

        $expense->delete();

        $remaining = Expense::count();
        $message .= "\n\nSisa {$remaining} pengeluaran";

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }
}

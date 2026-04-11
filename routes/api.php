<?php

use App\Http\Controllers\Api\V1\EmailController;
use App\Http\Controllers\Api\V1\ExpenseController;
use App\Http\Controllers\Api\V1\IncomeController;
use App\Http\Middleware\ValidateApiKey;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — List App Prem
|--------------------------------------------------------------------------
|
| Semua route di-prefix dengan /api/v1/ dan dilindungi oleh ValidateApiKey
| Bot Telegram/WhatsApp memanggil endpoint ini pakai header X-API-Key
|
*/

Route::prefix('v1')->middleware(ValidateApiKey::class)->group(function () {

    // ===== PEMASUKAN (Income) =====
    Route::prefix('incomes')->group(function () {
        Route::get('/', [IncomeController::class, 'index']);          // /list
        Route::post('/', [IncomeController::class, 'store']);         // /add
        Route::get('/today', [IncomeController::class, 'today']);     // /today
        Route::get('/yesterday', [IncomeController::class, 'yesterday']); // /yesterday
        Route::get('/week', [IncomeController::class, 'week']);       // /week
        Route::get('/month', [IncomeController::class, 'month']);     // /month
        Route::get('/summary', [IncomeController::class, 'summary']); // /summary
        Route::get('/top', [IncomeController::class, 'top']);         // /top
        Route::get('/stats', [IncomeController::class, 'stats']);     // /stats
        Route::delete('/last', [IncomeController::class, 'destroyLast']); // /undo
        Route::put('/{id}', [IncomeController::class, 'update']);     // /edit
        Route::delete('/{id}', [IncomeController::class, 'destroy']); // /delete
    });

    // ===== PENGELUARAN (Expense) =====
    Route::prefix('expenses')->group(function () {
        Route::get('/', [ExpenseController::class, 'index']);         // /spendlist
        Route::post('/', [ExpenseController::class, 'store']);        // /spend
        Route::get('/today', [ExpenseController::class, 'today']);    // /spendtoday
        Route::get('/month', [ExpenseController::class, 'month']);    // /spendmonth
        Route::delete('/{id}', [ExpenseController::class, 'destroy']); // /spenddelete
    });

    // ===== EMAIL =====
    Route::prefix('emails')->group(function () {
        Route::get('/', [EmailController::class, 'index']);           // /emaillist
        Route::post('/', [EmailController::class, 'store']);          // /email
        Route::put('/{id}', [EmailController::class, 'update']);      // /emailedit
        Route::delete('/{id}', [EmailController::class, 'destroy']); // /emaildelete
    });
});

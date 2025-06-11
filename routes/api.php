<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\PosTransactionController;
use App\Http\Controllers\MasterBudgetController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\BudgetDetailController;
use App\Http\Controllers\DashboardAdminController;
use App\Http\Controllers\DashboardSuperAdminController;
use App\Http\Controllers\RakController;
use App\Http\Controllers\RekaptulasiController;
use \App\Http\Controllers\ExportPdfController;
use Illuminate\Http\Request;

Route::middleware('auth:sanctum')->post('/tokens/create', function (Request $request) {
    $request->validate([
        'token_name' => 'required|string|max:255',
    ]);

    $token = $request->user()->createToken($request->token_name);

    return ['token' => $token->plainTextToken];
});
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/branch', [BranchController::class, 'index']);
    Route::get('/branch/{id}', [BranchController::class, 'show']);
    Route::post('/branch', [BranchController::class, 'store']);
    Route::put('/branch/{id}', [BranchController::class, 'update']);
    Route::delete('/branch/{id}', [BranchController::class, 'destroy']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/category', [CategoryController::class, 'index']);
    Route::get('/category/{id}', [CategoryController::class, 'show']);
    Route::post('/category', [CategoryController::class, 'store']);
    Route::put('/category/{id}', [CategoryController::class, 'update']);
    Route::delete('/category/{id}', [CategoryController::class, 'destroy']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [UserController::class, 'index']);
    Route::get('/user/{id}', [UserController::class, 'show']);
    Route::post('/user', [UserController::class, 'store']);
    Route::put('/user/{id}', [UserController::class, 'update']);
    Route::delete('/user/{id}', [UserController::class, 'destroy']);
});


Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/transaction', [TransactionController::class, 'index']);
    Route::get('/transaction-detail', [TransactionController::class, 'showDetail']);
    Route::get('/transaction-branch/{id}', [TransactionController::class, 'showByBranch']);
    Route::get('/transaction/{id}', [TransactionController::class, 'show']);
    Route::post('/transaction', [TransactionController::class, 'store']);
    Route::patch('/transaction/{id}', [TransactionController::class, 'update']);
    Route::patch('/transaction-lock/{id}', [TransactionController::class, 'lock']);
    Route::delete('/transaction/{id}', [TransactionController::class, 'destroy']);
});

Route::post('/pos/transaction', [TransactionController::class, 'store']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/pos-transaction', [PosTransactionController::class, 'index']);
    Route::get('/pos-transaction/{id}', [PosTransactionController::class, 'show']);
    Route::post('/pos-transaction', [PosTransactionController::class, 'store']);
    Route::put('/pos-transaction/{id}', [PosTransactionController::class, 'update']);
    Route::delete('/pos-transaction/{id}', [PosTransactionController::class, 'destroy']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/budget', [BudgetController::class, 'index']);
    Route::get('/budget/{id}', [BudgetController::class, 'show']);
    Route::post('/budget', [BudgetController::class, 'store']);
    Route::put('/budget/{id}', [BudgetController::class, 'update']);
    Route::delete('/budget/{id}', [BudgetController::class, 'destroy']);
    Route::get('/budget-branch/{id}', [BudgetController::class, 'showByBranch']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/budget-detail', [BudgetDetailController::class, 'index']);
    Route::get('/budget-detail/{id}', [BudgetDetailController::class, 'show']);
    Route::post('/budget-detail', [BudgetDetailController::class, 'store']);
    Route::put('/budget-detail/{id}', [BudgetDetailController::class, 'update']);
    Route::delete('/budget-detail/{id}', [BudgetDetailController::class, 'destroy']);
    Route::get('/budget-detail/budget/{id}', [BudgetDetailController::class, 'showByBudget']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/rak-branch/{id}', [RakController::class, 'showRakDetailByBranch']);
    Route::get('/rak/{id}', [RakController::class, 'showRakById']);
    Route::get('/rak-summary/{id}', [RakController::class, 'showRakSummaryByBranch']);
    Route::get('/rak', [RakController::class, 'showRakAll']);
    Route::patch('/rak-status/{id}', [RakController::class, 'updateStatus']);
    Route::patch('/rak/{id}', [RakController::class, 'updateRak']);
    Route::delete('/rak/{id}', [RakController::class, 'deleteRakByBranch']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/dashboard-summary/{id}', [DashboardAdminController::class, 'summary']);
    Route::get('/dashboard-trendchart/{id}', [DashboardAdminController::class, 'trendChart']);
    Route::get('/dashboard-trendchart-yearly/{id}', [DashboardAdminController::class, 'trendChartYearly']);
    Route::get('/dashboard-transactions/{id}', [DashboardAdminController::class, 'recentTransactions']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/superadmin/dashboard-summary', [DashboardSuperAdminController::class, 'summary']);
    Route::get('/superadmin/dashboard-trendline', [DashboardSuperAdminController::class, 'trendLine']);
    Route::get('/superadmin/dashboard-trendbar', [DashboardSuperAdminController::class, 'trendBar']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/rekaptulasi', [RekaptulasiController::class, 'index']);
    Route::get('/rekaptulasi/{branch_id}', [RekaptulasiController::class, 'showByBranch']);
});

//excel
Route::get('/rekapitulasi/export/excel/{branchId}', [RekaptulasiController::class, 'exportExcelByBranch']);
//pdf
Route::get('/rekapitulasi/export/pdf/{branchId}', [ExportPdfController::class, 'exportLaporanKeuanganLengkap']);
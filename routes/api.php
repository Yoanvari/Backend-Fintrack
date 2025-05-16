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
use Illuminate\Http\Request;

Route::post('/login', [AuthController::class, 'login']);

Route::get('/branch', [BranchController::class, 'index']);
Route::get('/branch/{id}', [BranchController::class, 'show']);
Route::post('/branch', [BranchController::class, 'store']);
Route::put('/branch/{id}', [BranchController::class, 'update']);
Route::delete('/branch/{id}', [BranchController::class, 'destroy']);

Route::get('/category', [CategoryController::class, 'index']);
Route::get('/category/{id}', [CategoryController::class, 'show']);
Route::post('/category', [CategoryController::class, 'store']);
Route::put('/category/{id}', [CategoryController::class, 'update']);
Route::delete('/category/{id}', [CategoryController::class, 'destroy']);

Route::get('/user', [UserController::class, 'index']);
Route::get('/user/{id}', [UserController::class, 'show']);
Route::post('/user', [UserController::class, 'store']);
Route::put('/user/{id}', [UserController::class, 'update']);
Route::delete('/user/{id}', [UserController::class, 'destroy']);

Route::get('/transaction', [TransactionController::class, 'index']);
Route::get('/transaction/{id}', [TransactionController::class, 'show']);
Route::post('/transaction', [TransactionController::class, 'store']);
Route::put('/transaction/{id}', [TransactionController::class, 'update']);
Route::delete('/transaction/{id}', [TransactionController::class, 'destroy']);

Route::get('/pos-transaction', [PosTransactionController::class, 'index']);
Route::get('/pos-transaction/{id}', [PosTransactionController::class, 'show']);
Route::post('/pos-transaction', [PosTransactionController::class, 'store']);
Route::put('/pos-transaction/{id}', [PosTransactionController::class, 'update']);
Route::delete('/pos-transaction/{id}', [PosTransactionController::class, 'destroy']);

Route::get('/master-budget', [MasterBudgetController::class, 'index']);
Route::get('/master-budget/{id}', [MasterBudgetController::class, 'show']);
Route::post('/master-budget', [MasterBudgetController::class, 'store']);
Route::put('/master-budget/{id}', [MasterBudgetController::class, 'update']);
Route::delete('/master-budget/{id}', [MasterBudgetController::class, 'destroy']);

Route::get('/budget', [BudgetController::class, 'index']);
Route::get('/budget/{id}', [BudgetController::class, 'show']);
Route::post('/budget', [BudgetController::class, 'store']);
Route::put('/budget/{id}', [BudgetController::class, 'update']);
Route::delete('/budget/{id}', [BudgetController::class, 'destroy']);

Route::get('/budget-detail', [BudgetDetailController::class, 'index']);
Route::get('/budget-detail/{id}', [BudgetDetailController::class, 'show']);
Route::post('/budget-detail', [BudgetDetailController::class, 'store']);
Route::put('/budget-detail/{id}', [BudgetDetailController::class, 'update']);
Route::delete('/budget-detail/{id}', [BudgetDetailController::class, 'destroy']);

Route::post('/tokens/create', function (Request $request) {
    $token = $request->user()->createToken($request->token_name);
 
    return ['token' => $token->plainTextToken];
});

Route::get('/dashboard-summary', [DashboardAdminController::class, 'summary']);
Route::get('/dashboard-trendchart', [DashboardAdminController::class, 'trendChart']);
Route::get('/dashboard-trendchart-yearly', [DashboardAdminController::class, 'trendChartYearly']);
Route::get('/dashboard-transactions', [DashboardAdminController::class, 'recentTransactions']);

Route::get('/superadmin/dashboard-summary', [DashboardSuperAdminController::class, 'summary']);
Route::get('/superadmin/dashboard-trendline', [DashboardSuperAdminController::class, 'trendLine']);
Route::get('/superadmin/dashboard-trendbar', [DashboardSuperAdminController::class, 'trendBar']);
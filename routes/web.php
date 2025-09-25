<?php

use App\Http\Controllers\AccountCodeController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AppliedCostController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\generalLedgerController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\SubsidiaryController;
use App\Http\Controllers\ZakatController;
use Illuminate\Support\Facades\Route;

// Guest Routes (Unauthenticated)
Route::middleware(['guest:admin,master'])->group(function () {
    Route::get('/', [AuthController::class, 'admin_login'])->name('login_page');
    Route::get('/login', [AuthController::class, 'admin_login'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Authenticated Routes (Admin or Master)
Route::middleware(['auth:admin,master'])->group(function () {
    // Dashboard and Logout
    Route::get('/dashboard', [DashboardController::class, 'dashboard_page'])->name('dashboard_page');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Admin-Specific Routes
    Route::middleware(['auth:admin'])->group(function () {
        Route::get('/admin_profile', [AdminController::class, 'admin_profile'])->name('admin_profile');
        Route::put('/admin_profile/update', [AdminController::class, 'admin_update'])->name('admin_update');
    });

    // Master-Specific Routes
    Route::middleware(['auth:master'])->group(function () {
        Route::get('/master_profile', [MasterController::class, 'master_profile'])->name('master_profile');
        Route::put('/master_profile/update', [MasterController::class, 'master_update'])->name('master_update');
    });

    // Employee Routes
    Route::get('/employee_page', [EmployeeController::class, 'employee_page'])->name('employee_page');

    // Voucher Routes
    Route::prefix('voucher')->group(function () {
        Route::get('/', [VoucherController::class, 'voucher_page'])->name('voucher_page');
        Route::post('/form', [VoucherController::class, 'voucher_form'])->name('voucher_form');
        Route::get('/{id}', [VoucherController::class, 'voucher_detail'])->name('voucher_detail');
        Route::get('/edit/{id}', [VoucherController::class, 'voucher_edit'])->name('voucher_edit');
        Route::delete('/{id}', [VoucherController::class, 'voucher_delete'])->name('voucher.delete');
        Route::put('/update/{id}', [VoucherController::class, 'voucher_update'])->name('voucher.update');
        Route::get('/{id}/pdf', [VoucherController::class, 'generatePdf'])->name('voucher_pdf');
    });

    // Stock Routes
    Route::prefix('stock')->group(function () {
        Route::get('/', [StockController::class, 'stock_page'])->name('stock_page');
        Route::get('/export', [StockController::class, 'export'])->name('stock.export');
        Route::get('/transfer/print', [StockController::class, 'printTransferForm'])->name('stock.transfer.print');
        Route::post('/recipe/store', [StockController::class, 'storeRecipe'])->name('recipe.store');
        Route::get('/recipe/{id}/ingredients', [StockController::class, 'getRecipeIngredients'])->name('recipe.ingredients');
        Route::put('/recipe/{id}', [StockController::class, 'updateRecipe'])->name('recipe.update');
        Route::delete('/recipe/{id}', [StockController::class, 'deleteRecipe'])->name('recipe.delete');
    });

    // Applied Cost Routes
    Route::prefix('applied-cost')->group(function () {
        Route::post('/applied_cost', [AppliedCostController::class, 'store'])->name('applied_cost.store');
        Route::post('/applied_cost/update', [AppliedCostController::class, 'update'])->name('applied_cost.update');
        Route::delete('/applied_cost/{id}', [AppliedCostController::class, 'delete'])->name('applied_cost.delete');
        Route::get('/applied_cost/history', [AppliedCostController::class, 'getHistory'])->name('applied_cost.history');
        Route::get('/applied_cost/{id}', [AppliedCostController::class, 'getDetail'])->name('applied_cost.detail');
    });

    // General Ledger Routes
    Route::prefix('general-ledger')->group(function () {
        Route::get('/', [generalLedgerController::class, 'generalledger_page'])->name('generalledger_page');
        Route::get('/print', [ExportController::class, 'generalledger_print'])->name('generalledger_print');
        Route::get('/trial-balance', [generalLedgerController::class, 'trialBalance_page'])->name('trialBalance_page');
        Route::get('/export/neraca-saldo', [ExportController::class, 'exportNeracaSaldo'])->name('export_neraca_saldo');
        Route::get('/income-statement', [generalLedgerController::class, 'incomeStatement_page'])->name('incomeStatement_page');
        Route::get('/export/income-statement', [ExportController::class, 'exportIncomeStatement'])->name('export_income_statement');
        Route::get('/balance-sheet', [generalLedgerController::class, 'balanceSheet_page'])->name('balanceSheet_page');
        Route::get('/export/balance-sheet', [ExportController::class, 'exportBalanceSheet'])->name('export_BalanceSheet');
    });

    // Account Code Routes
    Route::prefix('account')->group(function () {
        Route::get('/', [AccountCodeController::class, 'account_page'])->name('account_page');
        Route::post('/create', [AccountCodeController::class, 'create_account'])->name('account_create');
        Route::get('/edit/{accountCode}', [AccountCodeController::class, 'edit_account'])->name('accountCode_edit');
        Route::put('/update/{accountCode}', [AccountCodeController::class, 'update_account'])->name('account_update');
        Route::get('/pdf', [AccountCodeController::class, 'generatePdf'])->name('account-codes.pdf');
        Route::get('/excel', [AccountCodeController::class, 'exportExcel'])->name('account-codes.excel');
    });

    // Subsidiary Routes
    Route::prefix('subsidiary')->group(function () {
        Route::get('/utang', [SubsidiaryController::class, 'subsidiaryUtang_page'])->name('subsidiary_utang');
        Route::get('/piutang', [SubsidiaryController::class, 'subsidiaryPiutang_page'])->name('subsidiary_piutang');
        Route::post('/create', [SubsidiaryController::class, 'create_store'])->name('subsidiaries.store');
        Route::get('/utang/details', [SubsidiaryController::class, 'subsidiaryUtangDetails'])->name('subsidiaryUtang.details');
        Route::get('/piutang/details', [SubsidiaryController::class, 'subsidiaryPiutangDetails'])->name('subsidiaryPiutang.details');
        Route::get('/utang/pdf', [SubsidiaryController::class, 'generateUtangPdf'])->name('subsidiary_utang_pdf');
        Route::get('/piutang/pdf', [SubsidiaryController::class, 'generatePiutangPdf'])->name('subsidiary_piutang_pdf');
        Route::get('/excel', [SubsidiaryController::class, 'subsidiary_excel'])->name('subsidiary_excel');
        Route::put('/utang/{id}', [SubsidiaryController::class, 'utangUpdate'])->name('utangUpdate');
        Route::put('/piutang/{id}', [SubsidiaryController::class, 'piutangUpdate'])->name('piutangUpdate');
        Route::delete('/{id}', [SubsidiaryController::class, 'subsidiaryDelete'])->name('subsidiary.delete');
    });

    // Zakat Routes
    Route::prefix('zakat')->group(function () {
        Route::get('/', [ZakatController::class, 'zakat_page'])->name('zakat_page');
        Route::post('/calculate', [ZakatController::class, 'calculateZakat'])->name('zakat.calculate');
        Route::get('/export', [ZakatController::class, 'export'])->name('zakat.export');
    });

    // Company Routes
    Route::prefix('company')->group(function () {
        Route::get('/', [CompanyController::class, 'company_page'])->name('company_page');
        Route::get('/edit', [CompanyController::class, 'edit'])->name('company.edit');
        Route::post('/update', [CompanyController::class, 'update'])->name('company.update');
    });
});

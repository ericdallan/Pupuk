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

// Guest Routes - Single login page for both admin and master
Route::middleware(['guest:admin,master'])->group(function () {
    Route::get('/', [AuthController::class, 'admin_login'])->name('login_page');
    Route::get('/login', [AuthController::class, 'admin_login'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Authenticated Routes (admin or master)
Route::middleware(['auth:admin,master'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'dashboard_page'])->name('dashboard_page');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Admin Profile (admin only)
    Route::middleware(['auth:admin'])->group(function () {
        Route::get('/admin_profile', [AdminController::class, 'admin_profile'])->name('admin_profile');
        Route::put('/admin_profile/update', [AdminController::class, 'admin_update'])->name('admin_update');
    });

    // Master Profile (master only)
    Route::middleware(['auth:master'])->group(function () {
        Route::get('/master_profile', [MasterController::class, 'master_profile'])->name('master_profile');
        Route::put('/master_profile/update', [MasterController::class, 'master_update'])->name('master_update');
    });

    // Shared Routes for both admin and master
    Route::get('/employee_page', [EmployeeController::class, 'employee_page'])->name('employee_page');
    Route::get('/voucher_page', [VoucherController::class, 'voucher_page'])->name('voucher_page');
    Route::post('/voucher_form', [VoucherController::class, 'voucher_form'])->name('voucher_form');
    Route::get('/voucher/{id}', [VoucherController::class, 'voucher_detail'])->name('voucher_detail');
    Route::get('/voucher/edit/{id}', [VoucherController::class, 'voucher_edit'])->name('voucher_edit');
    Route::delete('/voucher/{id}', [VoucherController::class, 'voucher_delete'])->name('voucher.delete');
    Route::put('/voucher/update/{id}', [VoucherController::class, 'voucher_update'])->name('voucher.update');
    Route::get('/voucher/{id}/pdf', [VoucherController::class, 'generatePdf'])->name('voucher_pdf');
    Route::get('/stock_page', [StockController::class, 'stock_page'])->name('stock_page');
    Route::get('/stock/export', [StockController::class, 'export'])->name('stock.export');
    Route::get('stock/transfer/print', [StockController::class, 'printTransferForm'])->name('stock.transfer.print');
    Route::post('stock/recipe/store', [StockController::class, 'storeRecipe'])->name('recipe.store');
    Route::get('/recipe/{id}/ingredients', [StockController::class, 'getRecipeIngredients'])->name('recipe.ingredients');
    Route::put('/recipe/{id}', [StockController::class, 'updateRecipe'])->name('recipe.update');
    Route::delete('/recipe/{id}', [StockController::class, 'deleteRecipe'])->name('recipe.delete');
    Route::post('/applied-cost/store', [AppliedCostController::class, 'store'])->name('applied_cost.store');
    Route::get('/generalLedger_page', [generalLedgerController::class, 'generalledger_page'])->name('generalledger_page');
    Route::get('/general-ledger/print', [ExportController::class, 'generalledger_print'])->name('generalledger_print');
    Route::get('/trialBalance_page', [generalLedgerController::class, 'trialBalance_page'])->name('trialBalance_page');
    Route::get('/export/neraca-saldo', [ExportController::class, 'exportNeracaSaldo'])->name('export_neraca_saldo');
    Route::get('/incomeStatement_page', [generalLedgerController::class, 'incomeStatement_page'])->name('incomeStatement_page');
    Route::get('/export/income-statment', [ExportController::class, 'exportIncomeStatement'])->name('export_income_statement');
    Route::get('/balanceSheet_page', [generalLedgerController::class, 'balanceSheet_page'])->name('balanceSheet_page');
    Route::get('/export/balance-sheet', [ExportController::class, 'exportBalanceSheet'])->name('export_BalanceSheet');
    Route::get('/account_page', [AccountCodeController::class, 'account_page'])->name('account_page');
    Route::post('/account/create', [AccountCodeController::class, 'create_account'])->name('account_create');
    Route::get('/account_page/edit/{accountCode}', [AccountCodeController::class, 'edit_account'])->name('accoundeCode_edit');
    Route::put('/account_update/{accountCode}', [AccountCodeController::class, 'update_account'])->name('account_update');
    Route::get('/account-codes/pdf', [AccountCodeController::class, 'generatePdf'])->name('account-codes.pdf');
    Route::get('/account-codes/excel', [AccountCodeController::class, 'exportExcel'])->name('account-codes.excel');
    Route::get('/subsidiary_utang', [SubsidiaryController::class, 'subsidiaryUtang_page'])->name('subsidiary_utang');
    Route::get('/subsidiary_piutang', [SubsidiaryController::class, 'subsidiaryPiutang_page'])->name('subsidiary_piutang');
    Route::post('/subsidiary/create_store', [SubsidiaryController::class, 'create_store'])->name('subsidiaries.store');
    Route::get('/subsidiary_utang/details', [SubsidiaryController::class, 'subsidiaryUtangDetails'])->name('subsidiaryUtang.details');
    Route::get('/subsidiary_piuang/details', [SubsidiaryController::class, 'subsidiaryPiutangDetails'])->name('subsidiaryPiutang.details');
    Route::get('/subsidiary/utang/pdf', [SubsidiaryController::class, 'generateUtangPdf'])->name('subsidiary_utang_pdf');
    Route::get('/subsidiary/piutang/pdf', [SubsidiaryController::class, 'generatePiutangPdf'])->name('subsidiary_piutang_pdf');
    Route::get('/subsidiary/excel', [SubsidiaryController::class, 'subsidiary_excel'])->name('subsidiary_excel');
    Route::put('subsidiary/piutang/update/{id}', [SubsidiaryController::class, 'piutang_update'])->name('subsidiary_piutang.update');
    Route::put('subsidiary/utang/update/{id}', [SubsidiaryController::class, 'utang_update'])->name('subsidiary_utang.update');
    Route::delete('/subsidiary/{id}', [SubsidiaryController::class, 'subsidiaryDelete'])->name('subsidiary.delete');
    Route::get('/zakat_page', [ZakatController::class, 'zakat_page'])->name('zakat_page');
    Route::post('/zakat.calculate', [ZakatController::class, 'calculateZakat'])->name('zakat.calculate');
    Route::get('/zakat/export', [ZakatController::class, 'export'])->name('zakat.export');
    Route::get('/company_page', [CompanyController::class, 'company_page'])->name('company_page');
    Route::get('/company_page/edit', [CompanyController::class, 'edit'])->name('company.edit');
    Route::post('/company_page/update', [CompanyController::class, 'update'])->name('company.update');
});

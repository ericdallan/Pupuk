<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\Subsidiary;
use App\Models\VoucherDetails;
use App\Services\GeneralLedgerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class generalLedgerController extends Controller
{
    protected $generalLedgerService;

    public function __construct(GeneralLedgerService $generalLedgerService)
    {
        $this->generalLedgerService = $generalLedgerService;
    }

    /**
     * Display the General Ledger page
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function generalledger_page(Request $request)
    {
        try {
            $data = $this->generalLedgerService->prepareGeneralLedgerData($request->all());
            return view('generalLedger.generalLedger_page', $data);
        } catch (\Exception $e) {
            Log::error('General Ledger Page Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Gagal memuat halaman General Ledger']);
        }
    }

    /**
     * Display the Trial Balance page
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function trialBalance_page(Request $request)
    {
        try {
            $data = $this->generalLedgerService->prepareTrialBalanceData($request->all());
            return view('generalLedger.trialBalance_page', $data);
        } catch (\Exception $e) {
            Log::error('Trial Balance Page Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'An error occurred while generating the trial balance']);
        }
    }

    /**
     * Display the Income Statement page
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function incomeStatement_page(Request $request)
    {
        try {
            $data = $this->generalLedgerService->prepareIncomeStatementData($request->all());
            return view('generalLedger.incomeStatement_page', $data);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        } catch (\Exception $e) {
            Log::error('Income Statement Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat menghasilkan laporan laba rugi']);
        }
    }

    /**
     * Display the Balance Sheet page
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function balanceSheet_page(Request $request)
    {
        try {
            $data = $this->generalLedgerService->prepareBalanceSheetData($request->all());
            return view('generalLedger.balanceSheet_page', $data);
        } catch (\Exception $e) {
            Log::error('Balance Sheet Page Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Gagal memuat halaman neraca']);
        }
    }
}

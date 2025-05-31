<?php

namespace App\Http\Controllers;

use App\Exports\BalanceSheetExport;
use App\Exports\GeneralLedgerMultiSheetExport;
use App\Exports\IncomeStatementMultiSheetExport;
use App\Exports\TrialBalanceExport;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class ExportController extends Controller
{
    protected $exportService;

    public function __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    /**
     * Export General Ledger to Excel
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function generalledger_print(Request $request)
    {
        try {
            $data = $this->exportService->prepareGeneralLedgerData($request->all());
            return Excel::download(
                new GeneralLedgerMultiSheetExport($data['month'], $data['year'], $data['selectedAccountName']),
                $data['filename']
            );
        } catch (\Exception $e) {
            Log::error('General Ledger Export Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengekspor General Ledger.');
        }
    }

    /**
     * Export Income Statement to Excel
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportIncomeStatement(Request $request)
    {
        try {
            $data = $this->exportService->prepareIncomeStatementData($request->all());
            return Excel::download(
                new IncomeStatementMultiSheetExport($data['periodData'], $data['cumulativeData'], $data['year'], $data['month']),
                $data['filename']
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        } catch (\Exception $e) {
            Log::error('Export Income Statement Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengekspor laporan laba rugi');
        }
    }

    /**
     * Export Trial Balance to Excel
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportNeracaSaldo(Request $request)
    {
        try {
            $data = $this->exportService->prepareTrialBalanceData($request->all());
            return Excel::download(
                new TrialBalanceExport($data['month'], $data['year'], $data['accountBalances'], $data['accountNames']),
                $data['filename']
            );
        } catch (\Exception $e) {
            Log::error('Trial Balance Export Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengekspor neraca saldo.');
        }
    }

    /**
     * Export Balance Sheet to Excel
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportBalanceSheet(Request $request)
    {
        try {
            $data = $this->exportService->prepareBalanceSheetData($request->all());
            return Excel::download(
                new BalanceSheetExport(
                    $data['asetLancarData'],
                    $data['asetTetapData'],
                    $data['kewajibanData'],
                    $data['filteredEkuitasData'],
                    $data['startDate'],
                    $data['endDate']
                ),
                $data['filename']
            );
        } catch (\Exception $e) {
            Log::error('Balance Sheet Export Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengekspor neraca.');
        }
    }
}

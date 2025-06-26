<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\StockService;
use Carbon\Carbon;
use App\Exports\StockExport;
use App\Exports\StockImportTemplate; 
use App\Imports\StockImport;
use Maatwebsite\Excel\Facades\Excel;

class StockController extends Controller
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Display the Stock page
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function stock_page(Request $request)
    {
        try {
            $data = $this->stockService->prepareStockData($request->all());
            return view('stock.stock_page', $data);
        } catch (\Exception $e) {
            Log::error('Stock Page Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Gagal memuat halaman stok']);
        }
    }

    /**
     * Export stock data to Excel
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        try {
            $startDate = $request->input('start_date', Carbon::today()->startOfYear()->toDateString());
            $endDate = $request->input('end_date', Carbon::today()->toDateString());
            $data = $this->stockService->prepareExportData($startDate, $endDate);
            return Excel::download(new StockExport($data['startDate'], $data['endDate'], array_merge($data['stockData'], $data['transferStockData'], $data['usedStockData'])), 'stock_report_' . $data['startDate']->toDateString() . '_to_' . $data['endDate']->toDateString() . '.xlsx');
        } catch (\Exception $e) {
            Log::error('Stock Export Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Gagal mengekspor data stok']);
        }
    }
    /**
     * Download stock import template
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function importTemplate()
    {
        try {
            return Excel::download(new StockImportTemplate(), 'stock_import_template.xlsx');
        } catch (\Exception $e) {
            Log::error('Stock Import Template Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Gagal mengunduh template impor stok']);
        }
    }

    /**
     * Import stock data from Excel
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'stock_file' => 'required|file|mimes:xlsx,xls|max:2048', // Max 2MB
            ]);

            $this->stockService->importStockData($request->file('stock_file'));
            return redirect()->route('stock_page')->with('success', 'Data stok berhasil diimpor');
        } catch (\Exception $e) {
            Log::error('Stock Import Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Gagal mengimpor data stok']);
        }
    }

    /**
     * Fetch transactions for a specific stock item
     *
     * @param int $stockId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_transactions($stockId, Request $request)
    {
        try {
            $filter = $request->input('filter', '7_days');
            $transactions = $this->stockService->getTransactions((int) $stockId, $filter);
            return response()->json(['transactions' => $transactions]);
        } catch (\Exception $e) {
            Log::error('Get Transactions Error: ' . $e->getMessage());
            return response()->json(['transactions' => []], 500);
        }
    }
}

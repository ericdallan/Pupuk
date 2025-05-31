<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\StockService;
use Carbon\Carbon;
use App\Exports\StockExport;
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
            return Excel::download(new StockExport($data['startDate'], $data['endDate'], $data['stockData']), 'stock_report_' . $data['startDate']->toDateString() . '_to_' . $data['endDate']->toDateString() . '.xlsx');
        } catch (\Exception $e) {
            Log::error('Stock Export Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Gagal mengekspor data stok']);
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

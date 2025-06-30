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
use Barryvdh\DomPDF\Facade\Pdf;

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
            Log::debug('Stock Page Data:', [
                'stockData' => $data['stockData'],
                'transferStockData' => $data['transferStockData'],
                'usedStockData' => $data['usedStockData'],
                'startDate' => $data['startDate']->toDateString(),
                'endDate' => $data['endDate']->toDateString(),
                'table_filter' => $request->input('table_filter', 'all')
            ]);
            return view('stock.stock_page', $data);
        } catch (\Exception $e) {
            Log::error('Stock Page Error: ' . $e->getMessage(), ['exception' => $e]);
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
            Log::error('Stock Export Error: ' . $e->getMessage(), ['exception' => $e]);
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
            Log::error('Get Transactions Error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['transactions' => []], 500);
        }
    }

    /**
     * Generate and download a PDF transfer form
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function printTransferForm(Request $request)
    {
        try {
            $tableFilter = $request->input('table_filter', 'all');
            $data = $this->stockService->prepareTransferFormData($tableFilter);
            $pdf = Pdf::loadView('stock.stock_pdf', $data);
            return $pdf->download('Formulir_Pemindahan_Barang_' . now()->format('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            Log::error('Print Transfer Form Error: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->withErrors(['error' => 'Gagal menghasilkan formulir pemindahan']);
        }
    }

    /**
     * Store a new recipe
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeRecipe(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_name' => 'required|string|max:255|regex:/^[A-Za-z0-9\s]+$/',
                'product_size' => 'required|string|max:255', // Validation for product_size
                'transfer_stock_id.*' => 'required|exists:transfer_stocks,id',
                'quantity.*' => 'required|integer|min:1',
            ]);

            Log::debug('Store Recipe Input:', [
                'product_name' => $validated['product_name'],
                'product_size' => $validated['product_size'],
                'transfer_stock_ids' => $request->input('transfer_stock_id'),
                'quantities' => $request->input('quantity'),
            ]);

            $this->stockService->storeRecipe(
                $validated['product_name'],
                $request->input('transfer_stock_id'),
                $request->input('quantity'),
                $validated['product_size']
            );

            return redirect()->back()->with('success', 'Resep berhasil disimpan.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Store Recipe Validation Error: ' . $e->getMessage(), [
                'errors' => $e->errors(),
                'input' => $request->all(),
            ]);
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Store Recipe Error: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->withErrors(['error' => 'Gagal menyimpan resep: ' . $e->getMessage()]);
        }
    }
}

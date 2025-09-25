<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\StockService;
use Carbon\Carbon;
use App\Exports\StockExport;
use Illuminate\Support\Facades\DB;
use App\Exports\StockImportTemplate;
use App\Imports\StockImport;
use App\Models\AppliedCost;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class StockController extends Controller
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Display the Stock page with applied cost support
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function stock_page(Request $request)
    {
        try {
            $requestData = $request->all();

            // Better mode detection - check both query param and form data
            $mode = $request->input('mode', 'accounting');

            // Debug logging to see what's being received
            Log::info('Mode detection debug', [
                'request_mode' => $request->input('mode'),
                'query_mode' => $request->query('mode'),
                'all_params' => $request->all(),
                'final_mode' => $mode
            ]);

            $appliedCostId = $request->get('applied_cost_id', null);

            // Verify applied cost if provided
            if ($mode === 'management' && $appliedCostId) {
                $query = AppliedCost::where('id', $appliedCostId);
                if (Auth::guard('master')->check()) {
                    $query->where('master_id', Auth::guard('master')->id());
                }
                if (!$query->exists()) {
                    Log::warning('Applied cost not found', ['applied_cost_id' => $appliedCostId]);
                    $appliedCostId = null;
                    $requestData['applied_cost_id'] = null;
                }
            }

            // If management mode is selected but no applied cost is found, auto-select first available
            if ($mode === 'management' && !$appliedCostId) {
                $user = Auth::guard('master')->check() ? Auth::guard('master')->user() : Auth::user();
                $masterId = $user ? $user->id : null;

                $firstAppliedCost = AppliedCost::where('master_id', $masterId)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($firstAppliedCost) {
                    $appliedCostId = $firstAppliedCost->id;
                    $requestData['applied_cost_id'] = $appliedCostId;
                    Log::info('Auto-selected first applied cost', ['applied_cost_id' => $appliedCostId]);
                } else {
                    Log::warning('Management mode selected but no applied costs available');
                    // Could either fall back to accounting mode or show a message
                    $mode = 'accounting';
                }
            }

            // Prepare stock data
            if ($mode === 'management' && $appliedCostId) {
                $data = $this->stockService->prepareStockDataWithAppliedCost($requestData);
            } else {
                $data = $this->stockService->prepareStockData($requestData);
            }

            $data['currentMode'] = $mode;
            $data['selectedAppliedCostId'] = $appliedCostId;

            // Get applied cost history
            $user = Auth::guard('master')->check() ? Auth::guard('master')->user() : Auth::user();
            $masterId = $user ? $user->id : null;
            $data['appliedCostHistory'] = AppliedCost::with('details')
                ->where('master_id', $masterId)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
            $data['edit_cost_id'] = $request->input('edit_cost_id', null);
            $data['hasExistingAppliedCost'] = AppliedCost::where('master_id', $masterId)->exists();

            if (empty($data['stockData'])) {
                $data['stockData'] = [];
                Log::warning('No stock data available', ['request' => $requestData]);
            }

            Log::info('Stock page data prepared', [
                'mode' => $mode,
                'appliedCostId' => $appliedCostId,
                'stockDataCount' => count($data['stockData']),
                'appliedCostHistoryCount' => $data['appliedCostHistory']->count(),
                'userType' => Auth::guard('master')->check() ? 'master' : 'admin',
            ]);

            return view('stock.stock_page', $data);
        } catch (\Exception $e) {
            Log::error('Stock Page Error: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->withErrors(['error' => 'Gagal memuat halaman stok']);
        }
    }

    /**
     * Apply selected applied cost to calculations
     */
    public function applyAppliedCost(Request $request)
    {
        try {
            $request->validate([
                'applied_cost_id' => 'required|exists:applied_costs,id'
            ]);

            $masterId = Auth::guard('master')->id();
            $appliedCostId = $request->applied_cost_id;

            // Verifikasi applied cost
            $query = AppliedCost::where('id', $appliedCostId);
            if ($masterId) {
                $query->where('master_id', $masterId);
            }
            $appliedCost = $query->first();

            if (!$appliedCost) {
                return redirect()->back()->withErrors(['error' => 'Applied cost tidak ditemukan atau tidak memiliki akses.']);
            }

            // Redirect back to stock page with applied cost and management mode
            return redirect()->route('stock_page', array_merge($request->except(['applied_cost_id', '_token']), [
                'mode' => 'management',
                'applied_cost_id' => $appliedCostId
            ]))->with('success', 'Perhitungan beban telah diterapkan ke HPP.');
        } catch (\Exception $e) {
            Log::error('Apply Applied Cost Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Gagal menerapkan perhitungan beban.']);
        }
    }

    /**
     * Clear applied cost from calculations
     */
    public function clearAppliedCost(Request $request)
    {
        try {
            // Redirect back to stock page with accounting mode and no applied cost
            return redirect()->route('stock_page', array_merge($request->except(['_token']), [
                'mode' => 'accounting',
                'applied_cost_id' => null
            ]))->with('success', 'Perhitungan beban telah dihapus dari HPP.');
        } catch (\Exception $e) {
            Log::error('Clear Applied Cost Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Gagal menghapus perhitungan beban.']);
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
            return Excel::download(
                new StockExport($data['startDate'], $data['endDate'], $data),
                'stock_report_' . $data['startDate']->toDateString() . '_to_' . $data['endDate']->toDateString() . '.xlsx'
            );
        } catch (\Exception $e) {
            Log::error('Stock Export Error: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->withErrors(['error' => 'Gagal mengekspor data stok']);
        }
    }
}

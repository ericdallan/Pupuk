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
            // Get request data
            $requestData = $request->all();

            // Check if applied cost should be applied (management mode)
            $mode = $request->get('mode', 'accounting');
            $appliedCostId = $request->get('applied_cost_id', null);

            // Add mode and applied cost to request data
            $requestData['mode'] = $mode;
            $requestData['applied_cost_id'] = $appliedCostId;

            // Use the enhanced method if applied cost is requested
            if ($mode === 'management' && $appliedCostId) {
                $data = $this->stockService->prepareStockDataWithAppliedCost($requestData);
            } else {
                $data = $this->stockService->prepareStockData($requestData);
            }

            // Add mode and applied cost info to view data
            $data['currentMode'] = $mode;
            $data['selectedAppliedCostId'] = $appliedCostId;

            // Get applied cost history for the current master user with pagination
            $masterId = Auth::guard('master')->id();
            if ($masterId) {
                $data['appliedCostHistory'] = AppliedCost::with('details')
                    ->where('master_id', $masterId)
                    ->orderBy('created_at', 'desc')
                    ->paginate(10); // Paginasi untuk performa

                // Check if user already has an applied cost (limit to 1)
                $data['hasExistingAppliedCost'] = AppliedCost::where('master_id', $masterId)->exists();
            } else {
                $data['appliedCostHistory'] = collect([]);
                $data['hasExistingAppliedCost'] = false;
            }

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

            // Verify the applied cost belongs to the current master
            $appliedCost = AppliedCost::where('id', $appliedCostId)
                ->where('master_id', $masterId)
                ->first();

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

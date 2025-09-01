<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller as BaseController;

class DashboardController extends BaseController
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->middleware('auth');
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display the admin dashboard page
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function dashboard_page(Request $request)
    {
        try {
            // Ambil semua parameter yang diperlukan dari request
            $params = $request->only([
                'profit_trend_period',
                'profit_trend_year',
                'sales_trend_period',
                'sales_trend_year',
                'stock_qty_month',
                'stock_qty_year',
                'stock_amount_month',
                'stock_amount_year',
                'stock_qty_limit',
                'stock_qty_chart_type',
                'stock_amount_limit',
                'stock_amount_chart_type',
                'sales_month',
                'sales_year',
                'sales_qty_limit',
                'sales_qty_chart_type',
                'sales_profit_limit',
                'sales_profit_chart_type',
            ]);

            // Validasi parameter
            $validated = $request->validate([
                'profit_trend_period' => 'nullable|in:last_12_months,yearly',
                'profit_trend_year' => 'nullable|integer|min:1900',
                'sales_trend_period' => 'nullable|in:last_12_months,yearly',
                'sales_trend_year' => 'nullable|integer|min:1900',
                'stock_qty_month' => 'nullable|integer|between:1,12',
                'stock_qty_year' => 'nullable|integer|min:1900',
                'stock_amount_month' => 'nullable|integer|between:1,12',
                'stock_amount_year' => 'nullable|integer|min:1900',
                'stock_qty_limit' => 'nullable|integer|in:5,10',
                'stock_qty_chart_type' => 'nullable|in:bar,pie',
                'stock_amount_limit' => 'nullable|integer|in:5,10',
                'stock_amount_chart_type' => 'nullable|in:bar,pie',
                'sales_month' => 'nullable|integer|between:1,12',
                'sales_year' => 'nullable|integer|min:1900',
                'sales_qty_limit' => 'nullable|integer|in:5,10',
                'sales_qty_chart_type' => 'nullable|in:bar,pie',
                'sales_profit_limit' => 'nullable|integer|in:5,10',
                'sales_profit_chart_type' => 'nullable|in:bar,pie',
            ]);

            // Ambil data dashboard dari service
            $dashboardData = $this->dashboardService->getDashboardData($validated);

            // Kembalikan view dengan data
            return view('admin.dashboard_page', [
                'dashboardData' => $dashboardData,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation error in dashboard_page: ' . $e->getMessage(), ['errors' => $e->errors()]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Illuminate\View\ViewException $e) {
            Log::error('View error in dashboard_page: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('message', 'Halaman dashboard tidak ditemukan. Silakan hubungi administrator.');
        } catch (\Exception $e) {
            Log::error('Error rendering dashboard page: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('message', 'Terjadi kesalahan saat memuat data dashboard. Silakan coba lagi nanti.');
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display the admin dashboard page
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function dashboard_page(Request $request)
    {
        try {
            $params = [
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
            ];
            $dashboardData = $this->dashboardService->getDashboardData($params);
            return view('admin.dashboard_page', [
                'dashboardData' => $dashboardData,
            ]);
        } catch (\Exception $e) {
            Log::error('Error rendering dashboard page: ' . $e->getMessage());
            return back()->with('message', 'Error loading dashboard data');
        }
    }
}

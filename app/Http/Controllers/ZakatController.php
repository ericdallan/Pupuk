<?php

namespace App\Http\Controllers;

use App\Services\ZakatService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use App\Exports\ZakatExport;
use Maatwebsite\Excel\Facades\Excel;

class ZakatController extends Controller
{
    protected $zakatService;

    public function __construct(ZakatService $zakatService)
    {
        $this->zakatService = $zakatService;
    }

    /**
     * Display the zakat page
     *
     * @param Request|null $request
     * @return \Illuminate\View\View
     */
    public function zakat_page(Request $request = null)
    {
        $data = $this->zakatService->prepareZakatPageData($request);
        return view('zakat.zakat_page', $data);
    }

    /**
     * Calculate zakat based on the request
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function calculateZakat(Request $request)
    {
        try {
            $request->validate([
                'year' => 'nullable|numeric|min:1900|max:' . date('Y'),
                'month' => 'nullable|numeric|min:1|max:12',
                'calculation_method' => 'required|in:cara1,cara2',
            ]);

            $data = $this->zakatService->calculateZakat($request);
            return view('zakat.zakat_page', $data);
        } catch (\Exception $e) {
            Log::error('Zakat Calculation Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat menghitung zakat']);
        }
    }

    /**
     * Export zakat report to Excel
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        $params = $this->zakatService->getExportParameters($request);
        return Excel::download(
            new ZakatExport($params['year'], $params['month']),
            $params['filename']
        );
    }
}

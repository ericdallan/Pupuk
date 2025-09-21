<?php

namespace App\Http\Controllers;

use App\Services\AppliedCostService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class AppliedCostController extends Controller
{
    protected $appliedCostService;

    public function __construct(AppliedCostService $appliedCostService)
    {
        $this->middleware('auth:master');
        $this->appliedCostService = $appliedCostService;
    }

    /**
     * Store the accumulated beban and its details.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'total' => 'required|numeric|min:0',
                'beban_description' => 'required|array|min:1',
                'beban_description.*' => 'required|string|max:255',
                'beban_nominal' => 'required|array|min:1',
                'beban_nominal.*' => 'required|numeric|min:0',
            ]);

            $masterId = Auth::guard('master')->id();
            if (!$masterId) {
                throw new \Exception('Authenticated master user not found.');
            }

            // Combine descriptions and nominals into riwayat
            $riwayat = array_map(function ($description, $nominal) {
                return [
                    'description' => $description,
                    'nominal' => $nominal,
                ];
            }, $request->beban_description, $request->beban_nominal);

            // Verify total matches sum of nominals
            $calculatedTotal = array_sum(array_map('floatval', $request->beban_nominal));
            if (abs($calculatedTotal - $request->total) > 0.01) {
                throw ValidationException::withMessages(['total' => 'Total tidak sesuai dengan jumlah nominal beban.']);
            }

            $this->appliedCostService->storeBeban(
                $request->total,
                $riwayat,
                $masterId
            );

            return redirect()->route('stock_page')->with('success', 'Akumulasi beban berhasil disimpan.');
        } catch (ValidationException $e) {
            Log::error('Validation error in AppliedCostController::store: ' . json_encode($e->errors()));
            return redirect()->back()->withErrors($e->errors())->withInput()->with('modal_open', true);
        } catch (\Exception $e) {
            Log::error('Error in AppliedCostController::store: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan beban: ' . $e->getMessage())->withInput()->with('modal_open', true);
        }
    }
}

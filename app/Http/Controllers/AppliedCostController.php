<?php

namespace App\Http\Controllers;

use App\Services\AppliedCostService;
use App\Models\AppliedCost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AppliedCostController extends Controller
{
    protected $appliedCostService;

    public function __construct(AppliedCostService $appliedCostService)
    {
        $this->middleware('auth:master')->except(['getHistory', 'getDetail']);
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
            $masterId = Auth::guard('master')->id();
            if (!$masterId) {
                return redirect()->back()->with('error', 'User tidak terautentikasi.');
            }

            if (AppliedCost::where('master_id', $masterId)->exists()) {
                return redirect()->back()->with('error', 'Gagal menyimpan perhitungan beban: Hanya satu perhitungan beban yang diizinkan. Silakan edit atau hapus perhitungan yang ada di tab Riwayat Perhitungan.');
            }

            $request->validate([
                'total' => 'required|numeric|min:0',
                'beban_description' => 'required|array|min:1',
                'beban_description.*' => 'required|string|max:255',
                'beban_nominal' => 'required|array|min:1',
                'beban_nominal.*' => 'required|numeric|min:0',
            ]);

            $this->appliedCostService->createAppliedCost([
                'total' => $request->total,
                'beban_description' => $request->beban_description,
                'beban_nominal' => $request->beban_nominal,
            ], $masterId);

            return redirect()->back()->with('success', 'Perhitungan beban berhasil disimpan.');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error storing applied cost: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan perhitungan beban: ' . $e->getMessage());
        }
    }


    /**
     * Update an existing applied cost record.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:applied_costs,id',
                'total' => 'required|numeric|min:0',
                'beban_description' => 'required|array|min:1',
                'beban_description.*' => 'required|string|max:255',
                'beban_nominal' => 'required|array|min:1',
                'beban_nominal.*' => 'required|numeric|min:0',
            ]);

            $masterId = Auth::guard('master')->id();
            if (!$masterId) {
                return redirect()->back()->with('error', 'User tidak terautentikasi.');
            }

            $this->appliedCostService->updateAppliedCost($request->id, [
                'total' => $request->total,
                'beban_description' => $request->beban_description,
                'beban_nominal' => $request->beban_nominal,
            ], $masterId);

            return redirect()->back()->with('success', 'Perhitungan beban berhasil diperbarui.');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error updating applied cost: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui perhitungan beban: ' . $e->getMessage());
        }
    }

    /**
     * Delete an applied cost record.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        try {
            $masterId = Auth::guard('master')->id();
            if (!$masterId) {
                return response()->json(['success' => false, 'message' => 'User tidak terautentikasi.'], 403);
            }

            $this->appliedCostService->deleteAppliedCost($id, $masterId);

            return response()->json(['success' => true, 'message' => 'Perhitungan beban berhasil dihapus.']);
        } catch (\Exception $e) {
            Log::error('Error deleting applied cost: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menghapus perhitungan beban: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get the history of applied costs.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHistory(Request $request)
    {
        try {
            $history = $this->appliedCostService->getAppliedCostHistory();
            return response()->json([
                'success' => true,
                'data' => $history->items(),
                'total' => $history->total(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching applied cost history: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memuat riwayat perhitungan beban.'], 500);
        }
    }

    /**
     * Get details of a specific applied cost.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetail($id)
    {
        try {
            $cost = $this->appliedCostService->getAppliedCostDetail($id);
            return response()->json(['success' => true, 'data' => $cost]);
        } catch (\Exception $e) {
            Log::error('Error fetching applied cost detail: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memuat detail perhitungan beban.'], 500);
        }
    }
}

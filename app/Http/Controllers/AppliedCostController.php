<?php

namespace App\Http\Controllers;

use App\Services\AppliedCostService;
use App\Models\AppliedCost;
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
    /**
     * Get applied cost history for the modal
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHistory(Request $request)
    {
        try {
            $masterId = Auth::guard('master')->id();
            if (!$masterId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $query = AppliedCost::with(['details', 'master'])
                ->where('master_id', $masterId)
                ->orderBy('created_at', 'desc');

            // Apply date filter if provided
            if ($request->has('date_filter') && $request->date_filter) {
                $dateFilter = $request->date_filter;
                $now = now();

                switch ($dateFilter) {
                    case 'today':
                        $query->whereDate('created_at', $now->toDateString());
                        break;
                    case 'week':
                        $query->whereBetween('created_at', [$now->startOfWeek(), $now->endOfWeek()]);
                        break;
                    case 'month':
                        $query->whereMonth('created_at', $now->month)
                            ->whereYear('created_at', $now->year);
                        break;
                }
            }

            // Apply search if provided
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('details', function ($detailQuery) use ($search) {
                        $detailQuery->where('description', 'LIKE', '%' . $search . '%');
                    })->orWhere('total_nominal', 'LIKE', '%' . $search . '%')
                        ->orWhere('created_at', 'LIKE', '%' . $search . '%');
                });
            }

            $appliedCosts = $query->paginate($request->get('per_page', 10));

            $data = $appliedCosts->map(function ($appliedCost) {
                return [
                    'id' => $appliedCost->id,
                    'created_at' => $appliedCost->created_at->toDateTimeString(),
                    'total_nominal' => $appliedCost->total_nominal,
                    'details' => $appliedCost->details->map(function ($detail) {
                        return [
                            'description' => $detail->description,
                            'nominal' => $detail->nominal,
                        ];
                    }),
                    'status' => 'inactive', // You can add logic to determine active status
                    'master_name' => $appliedCost->master->name ?? 'Unknown',
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $appliedCosts->currentPage(),
                    'last_page' => $appliedCosts->lastPage(),
                    'per_page' => $appliedCosts->perPage(),
                    'total' => $appliedCosts->total(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching applied cost history: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch history'], 500);
        }
    }

    /**
     * Get specific applied cost details
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetail($id)
    {
        try {
            $masterId = Auth::guard('master')->id();
            if (!$masterId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $appliedCost = AppliedCost::with(['details', 'master'])
                ->where('id', $id)
                ->where('master_id', $masterId)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $appliedCost->id,
                    'created_at' => $appliedCost->created_at->toDateTimeString(),
                    'total_nominal' => $appliedCost->total_nominal,
                    'details' => $appliedCost->details->map(function ($detail) {
                        return [
                            'id' => $detail->id,
                            'description' => $detail->description,
                            'nominal' => $detail->nominal,
                        ];
                    }),
                    'master_name' => $appliedCost->master->name ?? 'Unknown',
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching applied cost detail: ' . $e->getMessage());
            return response()->json(['error' => 'Applied cost not found'], 404);
        }
    }

    /**
     * Delete applied cost record (optional)
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        try {
            $masterId = Auth::guard('master')->id();
            if (!$masterId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $appliedCost = AppliedCost::where('id', $id)
                ->where('master_id', $masterId)
                ->firstOrFail();

            // Delete related details first
            $appliedCost->details()->delete();

            // Delete the main record
            $appliedCost->delete();

            return response()->json([
                'success' => true,
                'message' => 'Applied cost deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting applied cost: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete applied cost'], 500);
        }
    }
}

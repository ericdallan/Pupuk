<?php

namespace App\Services;

use App\Models\AppliedCost;
use App\Models\AppliedCostDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AppliedCostService
{
    /**
     * Create a new applied cost record with its details.
     *
     * @param array $data
     * @param int $masterId
     * @return AppliedCost
     */
    public function createAppliedCost(array $data, $masterId)
    {
        return DB::transaction(function () use ($data, $masterId) {
            $appliedCost = AppliedCost::create([
                'total_nominal' => $data['total'],
                'master_id' => $masterId,
            ]);

            foreach ($data['beban_description'] as $index => $description) {
                AppliedCostDetail::create([
                    'applied_cost_id' => $appliedCost->id,
                    'description' => $description,
                    'nominal' => $data['beban_nominal'][$index],
                ]);
            }

            return $appliedCost;
        });
    }

    /**
     * Update an existing applied cost record with its details.
     *
     * @param int $id
     * @param array $data
     * @param int $masterId
     * @return AppliedCost
     * @throws \Exception
     */
    public function updateAppliedCost($id, array $data, $masterId)
    {
        return DB::transaction(function () use ($id, $data, $masterId) {
            $appliedCost = AppliedCost::where('id', $id)->where('master_id', $masterId)->firstOrFail();

            $appliedCost->update([
                'total_nominal' => $data['total'],
            ]);

            // Delete existing details
            AppliedCostDetail::where('applied_cost_id', $appliedCost->id)->delete();

            // Create new details
            foreach ($data['beban_description'] as $index => $description) {
                AppliedCostDetail::create([
                    'applied_cost_id' => $appliedCost->id,
                    'description' => $description,
                    'nominal' => $data['beban_nominal'][$index],
                ]);
            }

            return $appliedCost;
        });
    }

    /**
     * Delete an applied cost record and its details.
     *
     * @param int $id
     * @param int $masterId
     * @return void
     * @throws \Exception
     */
    public function deleteAppliedCost($id, $masterId)
    {
        DB::transaction(function () use ($id, $masterId) {
            $appliedCost = AppliedCost::where('id', $id)->where('master_id', $masterId)->firstOrFail();
            AppliedCostDetail::where('applied_cost_id', $appliedCost->id)->delete();
            $appliedCost->delete();
        });
    }

    /**
     * Get the history of applied costs with their details, with pagination.
     *
     * @param int|null $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAppliedCostHistory($perPage = 10)
    {
        $query = AppliedCost::with('details')->orderBy('created_at', 'desc');
        
        if (Auth::guard('master')->check()) {
            $query->where('master_id', Auth::guard('master')->id());
        }
        // If admin, no master_id filter is applied, allowing access to all records

        return $query->paginate($perPage);
    }

    /**
     * Get details of a specific applied cost.
     *
     * @param int $id
     * @return AppliedCost
     * @throws \Exception
     */
    public function getAppliedCostDetail($id)
    {
        $query = AppliedCost::with('details')->where('id', $id);
        
        if (Auth::guard('master')->check()) {
            $query->where('master_id', Auth::guard('master')->id());
        }
        // If admin, no master_id filter is applied

        return $query->firstOrFail();
    }
}
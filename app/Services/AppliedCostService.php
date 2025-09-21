<?php

namespace App\Services;

use App\Models\AppliedCost;
use App\Models\AppliedCostDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppliedCostService
{
    /**
     * Store the accumulated beban and its details.
     *
     * @param float $total
     * @param array $riwayat
     * @param int $masterId
     * @return void
     * @throws ValidationException
     */
    public function storeBeban(float $total, array $riwayat, int $masterId): void
    {
        // Validate that riwayat contains valid entries
        if (empty($riwayat)) {
            throw ValidationException::withMessages(['riwayat' => 'Riwayat tidak boleh kosong.']);
        }

        foreach ($riwayat as $beban) {
            if (!isset($beban['description']) || !isset($beban['nominal']) || !is_numeric($beban['nominal']) || $beban['nominal'] < 0) {
                throw ValidationException::withMessages(['riwayat' => 'Setiap beban harus memiliki deskripsi dan nominal yang valid.']);
            }
        }

        DB::beginTransaction();
        try {
            // Create AppliedCost record
            $appliedCost = AppliedCost::create([
                'total_nominal' => $total,
                'master_id' => $masterId,
            ]);

            // Create AppliedCostDetail records
            foreach ($riwayat as $beban) {
                AppliedCostDetail::create([
                    'applied_cost_id' => $appliedCost->id,
                    'nominal' => floatval($beban['nominal']),
                    'description' => $beban['description'],
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

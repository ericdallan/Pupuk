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
     * Each user can only have one applied cost record.
     *
     * @param float $total
     * @param array $riwayat
     * @param int $masterId
     * @return void
     * @throws ValidationException
     */
    public function storeBeban(float $total, array $riwayat, int $masterId): void
    {
        // Check if user already has an applied cost
        $existingAppliedCost = AppliedCost::where('master_id', $masterId)->first();

        if ($existingAppliedCost) {
            throw ValidationException::withMessages([
                'master_id' => 'Anda sudah memiliki perhitungan beban. Hapus perhitungan sebelumnya terlebih dahulu jika ingin membuat yang baru.'
            ]);
        }

        // Validate that riwayat contains valid entries
        if (empty($riwayat)) {
            throw ValidationException::withMessages(['riwayat' => 'Riwayat tidak boleh kosong.']);
        }

        foreach ($riwayat as $beban) {
            if (!isset($beban['description']) || !isset($beban['nominal']) || !is_numeric($beban['nominal']) || $beban['nominal'] < 0) {
                throw ValidationException::withMessages(['riwayat' => 'Setiap beban harus memiliki deskripsi dan nominal yang valid.']);
            }
        }

        // Verify total matches sum of nominals
        $calculatedTotal = array_sum(array_column($riwayat, 'nominal'));
        if (abs($calculatedTotal - $total) > 0.01) {
            throw ValidationException::withMessages(['total' => 'Total tidak sesuai dengan jumlah nominal beban.']);
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

    /**
     * Update existing applied cost (replace the single record)
     *
     * @param float $total
     * @param array $riwayat
     * @param int $masterId
     * @return void
     * @throws ValidationException
     */
    public function updateBeban(float $total, array $riwayat, int $masterId): void
    {
        // Find existing applied cost
        $existingAppliedCost = AppliedCost::where('master_id', $masterId)->first();

        if (!$existingAppliedCost) {
            throw ValidationException::withMessages([
                'master_id' => 'Tidak ditemukan perhitungan beban untuk diupdate.'
            ]);
        }

        // Validate that riwayat contains valid entries
        if (empty($riwayat)) {
            throw ValidationException::withMessages(['riwayat' => 'Riwayat tidak boleh kosong.']);
        }

        foreach ($riwayat as $beban) {
            if (!isset($beban['description']) || !isset($beban['nominal']) || !is_numeric($beban['nominal']) || $beban['nominal'] < 0) {
                throw ValidationException::withMessages(['riwayat' => 'Setiap beban harus memiliki deskripsi dan nominal yang valid.']);
            }
        }

        // Verify total matches sum of nominals
        $calculatedTotal = array_sum(array_column($riwayat, 'nominal'));
        if (abs($calculatedTotal - $total) > 0.01) {
            throw ValidationException::withMessages(['total' => 'Total tidak sesuai dengan jumlah nominal beban.']);
        }

        DB::beginTransaction();
        try {
            // Delete existing details
            $existingAppliedCost->details()->delete();

            // Update the applied cost total
            $existingAppliedCost->update([
                'total_nominal' => $total,
            ]);

            // Create new AppliedCostDetail records
            foreach ($riwayat as $beban) {
                AppliedCostDetail::create([
                    'applied_cost_id' => $existingAppliedCost->id,
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

    /**
     * Delete applied cost for a master
     *
     * @param int $masterId
     * @return void
     * @throws ValidationException
     */
    public function deleteBeban(int $masterId): void
    {
        $appliedCost = AppliedCost::where('master_id', $masterId)->first();

        if (!$appliedCost) {
            throw ValidationException::withMessages([
                'master_id' => 'Tidak ditemukan perhitungan beban untuk dihapus.'
            ]);
        }

        DB::beginTransaction();
        try {
            // Delete details first
            $appliedCost->details()->delete();

            // Delete the applied cost
            $appliedCost->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get applied cost for a master
     *
     * @param int $masterId
     * @return AppliedCost|null
     */
    public function getAppliedCostForMaster(int $masterId): ?AppliedCost
    {
        return AppliedCost::with('details')
            ->where('master_id', $masterId)
            ->first();
    }

    /**
     * Check if master has applied cost
     *
     * @param int $masterId
     * @return bool
     */
    public function hasAppliedCost(int $masterId): bool
    {
        return AppliedCost::where('master_id', $masterId)->exists();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppliedCostDetail extends Model
{
    protected $fillable = [
        'applied_cost_id',
        'nominal',
        'description',
    ];

    /**
     * Get the applied cost that owns this detail.
     */
    public function appliedCost()
    {
        return $this->belongsTo(AppliedCost::class, 'applied_cost_id');
    }
}

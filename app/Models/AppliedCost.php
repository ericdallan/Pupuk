<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AppliedCost extends Model
{
    protected $fillable = [
        'total_nominal',
        'master_id',
    ];

    /**
     * Get the details for this applied cost.
     */
    public function details(): HasMany
    {
        return $this->hasMany(AppliedCostDetail::class, 'applied_cost_id');
    }

    /**
     * Get the master user who created this applied cost.
     */
    public function master()
    {
        return $this->belongsTo(Master::class);
    }
}
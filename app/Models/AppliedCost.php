<?php

// app/Models/AppliedCost.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppliedCost extends Model
{
    use HasFactory;

    protected $table = 'applied_costs';

    protected $fillable = [
        'total_nominal',
        'master_id',
    ];

    protected $casts = [
        'total_nominal' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the applied cost details for this applied cost.
     */
    public function details(): HasMany
    {
        return $this->hasMany(AppliedCostDetail::class);
    }

    /**
     * Get the master that owns this applied cost.
     */
    public function master(): BelongsTo
    {
        return $this->belongsTo(Master::class);
    }

    /**
     * Scope to get applied costs for a specific master
     */
    public function scopeForMaster($query, $masterId)
    {
        return $query->where('master_id', $masterId);
    }

    /**
     * Scope to get applied costs within date range
     */
    public function scopeWithinDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get formatted total nominal
     */
    public function getFormattedTotalNominalAttribute()
    {
        return 'Rp. ' . number_format($this->total_nominal, 2, ',', '.');
    }
}
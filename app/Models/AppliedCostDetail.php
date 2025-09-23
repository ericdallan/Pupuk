<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppliedCostDetail extends Model
{
    use HasFactory;

    protected $table = 'applied_cost_details';

    protected $fillable = [
        'applied_cost_id',
        'nominal',
        'description',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the applied cost that owns this detail.
     */
    public function appliedCost(): BelongsTo
    {
        return $this->belongsTo(AppliedCost::class);
    }

    /**
     * Get formatted nominal
     */
    public function getFormattedNominalAttribute()
    {
        return 'Rp. ' . number_format($this->nominal, 2, ',', '.');
    }
}

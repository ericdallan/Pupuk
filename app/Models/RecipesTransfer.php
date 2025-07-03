<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class RecipeTransferStock extends Pivot
{
    protected $table = 'recipe_transfer_stock';

    protected $fillable = [
        'recipe_id',
        'transfer_stock_id',
        'quantity',
        'nominal',
    ];

    /**
     * Mendapatkan resep yang terkait
     */
    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * Mendapatkan transfer stock yang terkait
     */
    public function transferStock()
    {
        return $this->belongsTo(TransferStock::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class RecipesTransfer extends Pivot
{
    protected $table = 'recipe_transfer_stock';

    protected $fillable = [
        'recipe_id',
        'transfer_stock_id',
        'quantity',
        'nominal',
    ];

    public function recipe()
    {
        return $this->belongsTo(Recipes::class);
    }

    public function transferStock()
    {
        return $this->belongsTo(TransferStock::class);
    }
}

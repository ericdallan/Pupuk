<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipes extends Model
{
    protected $table = 'recipes';
    protected $fillable = ['product_name', 'size', 'used_stock_id', 'nominal'];

    public function transferStocks()
    {
        return $this->belongsToMany(
            TransferStock::class,
            'recipe_transfer_stock',
            'recipe_id',        // foreign key for current model
            'transfer_stock_id' // foreign key for related model
        )->withPivot('quantity', 'item', 'size')->withTimestamps();
    }

    public function usedStock()
    {
        return $this->belongsTo(UsedStock::class, 'used_stock_id');
    }

    public function recipeTransfers()
    {
        return $this->hasMany(RecipeTransfer::class, 'recipe_id');
    }
}

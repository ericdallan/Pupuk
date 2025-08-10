<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Transactions;
use App\Models\RecipesTransfer;

class TransferStock extends Model
{
    use HasFactory;

    protected $table = 'transfer_stocks';

    protected $fillable = [
        'item',
        'size',
        'quantity',
    ];

    public function transactions()
    {
        return $this->hasMany(Transactions::class, 'item', 'description');
    }

    public function recipes()
    {
        return $this->belongsToMany(Recipes::class, 'recipe_transfer_stock')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function recipeTransfers()
    {
        return $this->hasMany(RecipesTransfer::class, 'transfer_stock_id');
    }
}

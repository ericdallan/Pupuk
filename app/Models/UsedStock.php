<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsedStock extends Model
{
    protected $table = 'used_stocks';
    protected $fillable = ['item', 'size', 'quantity'];

    public function recipe()
    {
        return $this->hasOne(Recipes::class, 'used_stock_id', 'id'); // Inverse relationship
    }
}

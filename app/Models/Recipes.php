<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $fillable = [
        'used_stock_id',
        'product_name', // Opsional, jika Anda memutuskan untuk menyimpan nama produk di sini
    ];

    /**
     * Mendapatkan used stock yang terkait dengan resep ini
     */
    public function usedStock()
    {
        return $this->belongsTo(UsedStock::class);
    }

    /**
     * Mendapatkan transfer stocks yang digunakan dalam resep ini
     */
    public function transferStocks()
    {
        return $this->belongsToMany(TransferStock::class, 'recipe_transfer_stock')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}

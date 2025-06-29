<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UsedStock extends Model
{
    use HasFactory;

    protected $table = 'used_stocks';

    protected $fillable = [
        'item',
        'size',
        'quantity',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class TransferStock extends Model
{
    use HasFactory;

    protected $table = 'transfer_stocks';

    protected $fillable = [
        'item',
        'unit',
        'quantity',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}

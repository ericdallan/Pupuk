<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transactions extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = [
        'voucher_id',
        'description',
        'size',
        'quantity',
        'nominal',
    ];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    public function stock()
    {
        return $this->hasOne(Stock::class, 'item', 'description');
    }

    public function transferStock()
    {
        return $this->hasOne(TransferStock::class, 'item', 'description');
    }

    public function usedStock()
    {
        return $this->hasOne(UsedStock::class, 'item', 'description');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class voucherDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_id',
        'account_code',
        'account_name',
        'debit',
        'credit',
    ];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }
}
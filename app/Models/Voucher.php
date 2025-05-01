<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_number',
        'voucher_type',
        'voucher_date',
        'voucher_day',
        'prepared_by',
        'approved_by',
        'given_to',
        'transaction',
        'store',
        'invoice',
        'total_debit',
        'total_credit',
    ];

    protected $casts = [
        'voucher_date' => 'date',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
    ];

    public function voucherDetails(): HasMany
    {
        return $this->hasMany(voucherDetails::class, 'voucher_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transactions::class, 'voucher_id');
    }

    public function invoices(): HasOne
    {
        return $this->HasOne(Invoice::class, 'voucher_number', 'voucher_number');
    }

    public function invoice_payments()
    {
        return $this->hasMany(InvoicePayment::class, 'voucher_id', 'id');
    }
}

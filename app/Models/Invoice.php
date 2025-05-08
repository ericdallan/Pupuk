<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoices';

    protected $fillable = [
        'invoice',
        'voucher_number',
        'subsidiary_code',
        'status',
        'due_date',
        'total_amount',
        'remaining_amount',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class, 'voucher_number', 'voucher_number');
    }

    public function subsidiary(): BelongsTo
    {
        return $this->belongsTo(Subsidiary::class, 'subsidiary_code', 'subsidiary_code');
    }

    public function payment_vouchers(): HasMany
    {
        return $this->hasMany(InvoicePayment::class, 'invoice_id');
    }
}
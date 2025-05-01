<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subsidiary extends Model
{
    use HasFactory;

    protected $table = 'subsidiaries';

    protected $fillable = [
        'subsidiary_code',
        'account_name',
        'account_code',
        'store_name',
    ];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'subsidiary_code', 'subsidiary_code');
    }
    
    public function invoicePayments()
    {
        return $this->hasManyThrough(InvoicePayment::class, Invoice::class, 'subsidiary_code', 'invoice_id', 'subsidiary_code', 'id');
    }
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $exists = self::where('account_code', $model->account_code)
                ->where('store_name', $model->store_name)
                ->exists();
            if ($exists) {
                throw new \Exception('Kombinasi account_code dan store_name sudah ada');
            }
        });
    }
}

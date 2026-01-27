<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CryptoInvoice extends Model
{
    protected $fillable = [
        'order_id',
        'crypto_currency',
        'wallet_address',
        'amount_crypto',
        'amount_usd',
        'status',
        'expires_at',
        'confirmed_at',
        'blockchain_data',
    ];

    protected $casts = [
        'amount_crypto' => 'decimal:8',
        'amount_usd' => 'decimal:2',
        'expires_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'blockchain_data' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
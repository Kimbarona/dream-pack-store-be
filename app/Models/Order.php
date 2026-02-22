<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Order extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    public const STATUSES = [
        'pending_payment' => 'Pending Payment',
        'processing' => 'Processing',
        'to_ship' => 'To Ship',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ];

    protected $fillable = [
        'id',
        'user_id',
        'order_number',
        'subtotal',
        'tax_amount',
        'shipping_amount',
        'total',
        'status',
        'shipping_address',
        'billing_address',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'shipping_address' => 'array',
        'billing_address' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->id)) {
                $order->id = (string) Str::uuid();
            }
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(uniqid());
            }
            if (empty($order->status)) {
                $order->status = 'pending_payment';
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function cryptoInvoices(): HasMany
    {
        return $this->hasMany(CryptoInvoice::class);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, $limit = 10)
    {
        return $query->latest()->limit($limit);
    }

    public function isPaid()
    {
        return in_array($this->status, ['processing', 'to_ship', 'shipped', 'delivered']);
    }

    public function isPendingPayment()
    {
        return $this->status === 'pending_payment';
    }

    public function isPaidUnconfirmed()
    {
        return false;
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['pending_payment', 'processing']);
    }

    public function getFormattedTotalAttribute()
    {
        return number_format($this->total, 2);
    }

    public function getStatusLabelAttribute()
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getCanBePaidAttribute()
    {
        return $this->status === 'pending_payment';
    }

    public function getIsCompletedAttribute()
    {
        return in_array($this->status, ['delivered', 'cancelled']);
    }

    public function getTotalItemsAttribute()
    {
        return $this->items()->sum('quantity');
    }

    public function getLatestPaymentAttribute()
    {
        return $this->payments()->latest()->first();
    }

    public function getHasSuccessfulPaymentAttribute()
    {
        return $this->payments()->where('status', 'completed')->exists();
    }

    public function transitionStatus($newStatus)
    {
        if (!isset(self::STATUSES[$newStatus])) {
            throw new \InvalidArgumentException("Invalid order status: {$newStatus}");
        }

        $oldStatus = $this->status;
        $this->status = $newStatus;
        $this->save();

        Log::info("Order status changed", [
            'order_id' => $this->getKey(),
            'order_number' => $this->order_number,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'user_id' => \Illuminate\Support\Facades\Auth::check() ? \Illuminate\Support\Facades\Auth::id() : null,
        ]);

        return $this;
    }

    public function cancel()
    {
        if (!$this->canBeCancelled()) {
            throw new \Exception("Order cannot be cancelled in current status: {$this->status}");
        }

        return $this->transitionStatus('cancelled');
    }

    public function markAsProcessing()
    {
        return $this->transitionStatus('processing');
    }

    public function markAsShipped()
    {
        return $this->transitionStatus('shipped');
    }

    public function markAsToShip()
    {
        return $this->transitionStatus('to_ship');
    }

    public function markAsDelivered()
    {
        return $this->transitionStatus('delivered');
    }

    public function markAsPaidUnconfirmed()
    {
        return $this->transitionStatus('processing');
    }

    public function markAsPaidConfirmed()
    {
        return $this->transitionStatus('processing');
    }

    public function recalculateTotals()
    {
        $this->subtotal = $this->items()->sum('total_price');
        
        // Calculate tax as 18% of (subtotal + shipping)
        $this->tax_amount = round(($this->subtotal + $this->shipping_amount) * 0.18, 2);
        
        $this->total = $this->subtotal + $this->tax_amount + $this->shipping_amount;
        $this->save();
        
        return $this;
    }

    public function scopeWithPayments($query)
    {
        return $query->with(['payments' => function ($query) {
            $query->latest();
        }]);
    }

    public function scopeWithCryptoInvoices($query)
    {
        return $query->with(['cryptoInvoices' => function ($query) {
            $query->latest();
        }]);
    }
}
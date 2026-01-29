<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'id',
        'order_id',
        'product_id',
        'product_title',
        'product_sku',
        'quantity',
        'unit_price',
        'total_price',
        'size',
        'chosen_color',
        'pieces_per_package',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'chosen_color' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (empty($item->total_price)) {
                $item->total_price = $item->unit_price * $item->quantity;
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getEffectivePriceAttribute()
    {
        return $this->unit_price;
    }

    public function getFormattedUnitPriceAttribute()
    {
        return number_format($this->unit_price, 2);
    }

    public function getFormattedSalePriceAttribute()
    {
        return null;
    }

    public function getFormattedTotalPriceAttribute()
    {
        return number_format($this->total_price, 2);
    }

    public function getChosenColorLabelAttribute()
    {
        if (is_array($this->chosen_color)) {
            return $this->chosen_color['value'] ?? null;
        }
        return $this->chosen_color;
    }

    public function getIsOnSaleAttribute()
    {
        return false;
    }

    public function getDiscountPercentageAttribute()
    {
        return 0;
    }

    public function recalculateTotal()
    {
        $this->total_price = $this->quantity * $this->effective_price;
        $this->save();
        
        return $this;
    }

    public function getSnapshotData()
    {
        return [
            'product_title' => $this->product_title,
            'product_sku' => $this->product_sku,
            'unit_price' => $this->unit_price,
            'size' => $this->size,
            'chosen_color' => $this->chosen_color,
            'pieces_per_package' => $this->pieces_per_package,
        ];
    }

    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'effective_price' => $this->effective_price,
            'formatted_unit_price' => $this->formatted_unit_price,
            'formatted_total_price' => $this->formatted_total_price,
            'chosen_color_label' => $this->chosen_color_label,
            'is_on_sale' => $this->is_on_sale,
            'discount_percentage' => $this->discount_percentage,
        ]);
    }
}
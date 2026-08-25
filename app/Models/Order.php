<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'customer_name',
        'base_total',
        'unique_code',
        'grand_total',
        'cash_received',
        'cash_change',
        'discount',
        'discount_percent',
        'coupon_code',
        'tax',
        'tax_percent',
        'service',
        'service_percent',
        'notes',
        'payment_proof',
        'settlement_type',
        'settled_by',
        'status',
        'payment_method',
        'void_reason',
        'void_by',
        'voided_at',
    ];

    protected $casts = [
        'base_total' => 'decimal:2',
        'unique_code' => 'integer',
        'grand_total' => 'decimal:2',
        'cash_received' => 'decimal:2',
        'cash_change' => 'decimal:2',
        'discount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'tax' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'service' => 'decimal:2',
        'service_percent' => 'decimal:2',
        'voided_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function settledByUser()
    {
        return $this->belongsTo(User::class, 'settled_by');
    }

    public function voidedByUser()
    {
        return $this->belongsTo(User::class, 'void_by');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_code', 'code');
    }

    public function getPaymentProofUrlAttribute()
    {
        if (!$this->payment_proof) {
            return null;
        }
        if (filter_var($this->payment_proof, FILTER_VALIDATE_URL)) {
            return $this->payment_proof;
        }
        return asset('storage/' . $this->payment_proof);
    }
}

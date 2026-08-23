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
        'tax',
        'tax_percent',
        'service',
        'service_percent',
        'notes',
        'status',
        'payment_method',
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
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}

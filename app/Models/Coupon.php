<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'category_id',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'max_discount_amount',
        'usage_limit',
        'used_count',
        'is_active',
        'expires_at',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Calculate discount amount based on items in cart and base total.
     */
    public function calculateDiscount(array $items = [], float $baseTotal = 0): array
    {
        if (!$this->is_active) {
            return ['valid' => false, 'message' => 'Kupon voucher sudah tidak aktif!'];
        }

        if ($this->expires_at && Carbon::now()->isAfter($this->expires_at)) {
            return ['valid' => false, 'message' => 'Kupon voucher sudah kedaluwarsa!'];
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return ['valid' => false, 'message' => 'Kuota penggunaan kupon voucher sudah habis!'];
        }

        if ($baseTotal < floatval($this->min_order_amount)) {
            return [
                'valid' => false,
                'message' => 'Minimal belanja untuk kupon ini adalah Rp ' . number_format($this->min_order_amount, 0, ',', '.')
            ];
        }

        // Check if coupon is category-specific
        $applicableSubtotal = $baseTotal;
        if ($this->category_id) {
            $applicableSubtotal = 0;
            $categoryName = $this->category ? $this->category->name : 'Kategori Khusus';

            foreach ($items as $item) {
                $product = Product::find($item['id'] ?? null);
                if ($product && $product->category_id == $this->category_id) {
                    $applicableSubtotal += ($item['price'] * $item['qty']);
                }
            }

            if ($applicableSubtotal <= 0) {
                return [
                    'valid' => false,
                    'message' => "Kupon ini khusus untuk kategori \"{$categoryName}\". Keranjang Anda belum berisi menu dari kategori tersebut."
                ];
            }
        }

        $discount = 0;
        $discountPercent = 0;

        if ($this->discount_type === 'PERCENT') {
            $discountPercent = floatval($this->discount_value);
            $discount = $applicableSubtotal * ($discountPercent / 100);
            if ($this->max_discount_amount && $discount > floatval($this->max_discount_amount)) {
                $discount = floatval($this->max_discount_amount);
            }
        } else {
            // FIXED AMOUNT
            $discount = min($applicableSubtotal, floatval($this->discount_value));
            $discountPercent = ($baseTotal > 0) ? round(($discount / $baseTotal) * 100, 2) : 0;
        }

        return [
            'valid' => true,
            'coupon_code' => $this->code,
            'title' => $this->title,
            'category_id' => $this->category_id,
            'category_name' => $this->category ? $this->category->name : null,
            'discount_type' => $this->discount_type,
            'discount_value' => floatval($this->discount_value),
            'discount_amount' => round($discount, 2),
            'discount_percent' => $discountPercent,
            'applicable_subtotal' => $applicableSubtotal,
            'message' => 'Kupon berhasil diterapkan!'
        ];
    }
}

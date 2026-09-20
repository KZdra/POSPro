<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'opening_cash',
        'cash_sales',
        'non_cash_sales',
        'expected_cash',
        'actual_cash',
        'difference',
        'status',
        'opened_at',
        'closed_at',
        'notes',
    ];

    protected $casts = [
        'opening_cash' => 'decimal:2',
        'cash_sales' => 'decimal:2',
        'non_cash_sales' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'actual_cash' => 'decimal:2',
        'difference' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function movements()
    {
        return $this->hasMany(CashMovement::class, 'cash_shift_id')->latest();
    }

    public function totalCashIn(): float
    {
        return floatval($this->movements()->where('type', 'CASH_IN')->sum('amount'));
    }

    public function totalCashOut(): float
    {
        return floatval($this->movements()->where('type', 'CASH_OUT')->sum('amount'));
    }

    public function isOpen(): bool
    {
        return $this->status === 'OPEN';
    }
}

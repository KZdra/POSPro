<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'color',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getColorBadgeClassAttribute(): string
    {
        return match ($this->color) {
            'amber' => 'bg-amber-50 text-amber-800 border-amber-200',
            'orange' => 'bg-orange-50 text-orange-800 border-orange-200',
            'emerald', 'green' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            'teal', 'cyan' => 'bg-teal-50 text-teal-800 border-teal-200',
            'purple' => 'bg-purple-50 text-purple-800 border-purple-200',
            'rose', 'pink', 'red' => 'bg-rose-50 text-rose-800 border-rose-200',
            'yellow' => 'bg-yellow-50 text-yellow-800 border-yellow-200',
            'slate', 'dark' => 'bg-slate-100 text-slate-800 border-slate-300',
            default => 'bg-blue-50 text-blue-800 border-blue-200',
        };
    }

    public function getColorIconBgAttribute(): string
    {
        return match ($this->color) {
            'amber' => 'bg-amber-100 text-amber-700',
            'orange' => 'bg-orange-100 text-orange-700',
            'emerald', 'green' => 'bg-emerald-100 text-emerald-700',
            'teal', 'cyan' => 'bg-teal-100 text-teal-700',
            'purple' => 'bg-purple-100 text-purple-700',
            'rose', 'pink', 'red' => 'bg-rose-100 text-rose-700',
            'yellow' => 'bg-yellow-100 text-yellow-700',
            'slate', 'dark' => 'bg-slate-200 text-slate-700',
            default => 'bg-blue-100 text-blue-700',
        };
    }

    public function getColorSolidBadgeAttribute(): string
    {
        return match ($this->color) {
            'amber' => 'bg-amber-600 text-white',
            'orange' => 'bg-orange-600 text-white',
            'emerald', 'green' => 'bg-emerald-600 text-white',
            'teal', 'cyan' => 'bg-teal-600 text-white',
            'purple' => 'bg-purple-600 text-white',
            'rose', 'pink', 'red' => 'bg-rose-600 text-white',
            'yellow' => 'bg-yellow-500 text-slate-900',
            'slate', 'dark' => 'bg-slate-800 text-white',
            default => 'bg-blue-600 text-white',
        };
    }
}

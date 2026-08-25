<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Category;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::with('category')->latest()->get();
        $categories = Category::orderBy('name')->get();
        return view('admin.coupons.index', compact('coupons', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'title' => 'required|string|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'discount_type' => 'required|in:PERCENT,FIXED',
            'discount_value' => 'required|numeric|min:0.01',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ], [
            'code.unique' => 'Kode kupon promo sudah terdaftar, silakan gunakan kode lain!',
        ]);

        Coupon::create([
            'code' => strtoupper(trim($request->code)),
            'title' => trim($request->title),
            'category_id' => $request->filled('category_id') ? $request->category_id : null,
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
            'min_order_amount' => $request->min_order_amount ?? 0,
            'max_discount_amount' => $request->max_discount_amount,
            'usage_limit' => $request->usage_limit,
            'is_active' => $request->boolean('is_active', true),
            'expires_at' => $request->filled('expires_at') ? Carbon::parse($request->expires_at) : null,
        ]);

        return redirect()->route('coupons.index')->with('success', 'Kupon voucher promo baru berhasil ditambahkan!');
    }

    public function update(Request $request, Coupon $coupon)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'title' => 'required|string|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'discount_type' => 'required|in:PERCENT,FIXED',
            'discount_value' => 'required|numeric|min:0.01',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        $coupon->update([
            'code' => strtoupper(trim($request->code)),
            'title' => trim($request->title),
            'category_id' => $request->filled('category_id') ? $request->category_id : null,
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
            'min_order_amount' => $request->min_order_amount ?? 0,
            'max_discount_amount' => $request->max_discount_amount,
            'usage_limit' => $request->usage_limit,
            'is_active' => $request->boolean('is_active'),
            'expires_at' => $request->filled('expires_at') ? Carbon::parse($request->expires_at) : null,
        ]);

        return redirect()->route('coupons.index')->with('success', 'Data kupon promo berhasil diperbarui!');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return redirect()->route('coupons.index')->with('success', 'Kupon promo berhasil dihapus!');
    }
}

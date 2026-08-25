<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Setting;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        $today = Carbon::today();
        
        // Key Metrics
        $todaySales = Order::whereDate('created_at', $today)->where('status', 'PAID')->sum('grand_total');
        $todayOrdersCount = Order::whereDate('created_at', $today)->where('status', 'PAID')->count();
        $totalProducts = Product::where('is_active', true)->count();
        $lowStockProducts = Product::where('manage_stock', true)->where('stock', '<=', 5)->get();

        // Monthly sales
        $monthSales = Order::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->where('status', 'PAID')
            ->sum('grand_total');

        // Recent 5 Orders
        $recentOrders = Order::with('items')->latest()->take(5)->get();

        // Payment method breakdown (Today)
        $cashSalesToday = Order::whereDate('created_at', $today)->where('status', 'PAID')->where('payment_method', 'CASH')->sum('grand_total');
        $qrisSalesToday = Order::whereDate('created_at', $today)->where('status', 'PAID')->where('payment_method', 'QRIS')->sum('grand_total');

        return view('dashboard', compact(
            'todaySales',
            'todayOrdersCount',
            'totalProducts',
            'lowStockProducts',
            'monthSales',
            'recentOrders',
            'cashSalesToday',
            'qrisSalesToday'
        ));
    }

    public function history(Request $request)
    {
        $query = Order::with(['items', 'user', 'voidedByUser', 'settledByUser'])->latest();

        // Date Range Filtering
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('date') && !$request->filled('start_date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->get();
        $totalRevenue = $orders->where('status', 'PAID')->sum('grand_total');
        $paidCount = $orders->where('status', 'PAID')->count();
        $cashCount = $orders->where('status', 'PAID')->where('payment_method', 'CASH')->count();
        $qrisCount = $orders->where('status', 'PAID')->where('payment_method', 'QRIS')->count();
        $voidCount = $orders->where('status', 'VOID')->count();

        return view('admin.history', compact('orders', 'totalRevenue', 'paidCount', 'cashCount', 'qrisCount', 'voidCount'));
    }

    public function voidOrder(Request $request, $orderId)
    {
        $order = Order::with('items.product')->where('order_id', $orderId)->firstOrFail();

        if ($order->status === 'VOID') {
            return back()->with('error', 'Pesanan ini sudah dibatalkan (VOID) sebelumnya.');
        }

        $request->validate([
            'void_reason' => 'required|string|max:500',
        ], [
            'void_reason.required' => 'Alasan pembatalan / void wajib diisi!',
        ]);

        // 1. Restock items that have manage_stock enabled
        foreach ($order->items as $item) {
            $product = $item->product ?? Product::find($item->product_id);
            if ($product && $product->manage_stock) {
                $product->increment('stock', $item->qty);
            }
        }

        // 2. Mark order as VOID
        $order->update([
            'status' => 'VOID',
            'void_reason' => trim($request->void_reason),
            'void_by' => auth()->id(),
            'voided_at' => now(),
        ]);

        return back()->with('success', "Order #{$order->order_id} berhasil dibatalkan (VOID) dan stok produk telah dikembalikan.");
    }

    public function exportPdf(Request $request)
    {
        $query = Order::with('items', 'user')->latest();

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->get();

        // Summary Calculations
        $totalGross = $orders->where('status', 'PAID')->sum('base_total');
        $totalDiscount = $orders->where('status', 'PAID')->sum('discount');
        $totalTax = $orders->where('status', 'PAID')->sum('tax');
        $totalService = $orders->where('status', 'PAID')->sum('service');
        $totalNetRevenue = $orders->where('status', 'PAID')->sum('grand_total');
        $paidOrdersCount = $orders->where('status', 'PAID')->count();
        $cashTotal = $orders->where('status', 'PAID')->where('payment_method', 'CASH')->sum('grand_total');
        $cashCount = $orders->where('status', 'PAID')->where('payment_method', 'CASH')->count();
        $qrisTotal = $orders->where('status', 'PAID')->where('payment_method', 'QRIS')->sum('grand_total');
        $qrisCount = $orders->where('status', 'PAID')->where('payment_method', 'QRIS')->count();

        // Best-Selling Items in this filtered batch
        $paidOrderIds = $orders->where('status', 'PAID')->pluck('id');
        $topProducts = OrderItem::whereIn('order_id', $paidOrderIds)
            ->select('product_name', DB::raw('SUM(qty) as total_qty'), DB::raw('SUM(subtotal) as total_amount'))
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        $store = [
            'name' => Setting::get('store_name', 'POSPRO STORE'),
            'address' => Setting::get('store_address', 'Jl. Sudirman No. 123, Jakarta Selatan'),
            'phone' => Setting::get('store_phone', '0812-3456-7890'),
        ];

        $pdf = Pdf::loadView('admin.reports.sales_pdf', compact(
            'orders',
            'startDate',
            'endDate',
            'totalGross',
            'totalDiscount',
            'totalTax',
            'totalService',
            'totalNetRevenue',
            'paidOrdersCount',
            'cashTotal',
            'cashCount',
            'qrisTotal',
            'qrisCount',
            'topProducts',
            'store'
        ))->setPaper('a4', 'portrait');

        $filename = 'Laporan-Penjualan-' . date('Ymd-His') . '.pdf';
        
        if ($request->has('stream')) {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }
}

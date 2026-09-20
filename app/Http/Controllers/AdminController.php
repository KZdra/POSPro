<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\Coupon;
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
        $lowStockProducts = Product::where('manage_stock', true)->whereRaw('stock <= min_stock')->get();

        // Gross Profit (HPP Margin)
        $todayPaidOrderIds = Order::whereDate('created_at', $today)->where('status', 'PAID')->pluck('id');
        $todayHpp = OrderItem::whereIn('order_id', $todayPaidOrderIds)->sum(DB::raw('cost_price * qty'));
        $todayGrossProfit = max(0, $todaySales - $todayHpp);
        $todayProfitMargin = ($todaySales > 0) ? round(($todayGrossProfit / $todaySales) * 100, 1) : 0;

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
            'todayHpp',
            'todayGrossProfit',
            'todayProfitMargin',
            'monthSales',
            'recentOrders',
            'cashSalesToday',
            'qrisSalesToday'
        ));
    }

    public function history(Request $request)
    {
        $query = Order::with(['items', 'user', 'customer', 'voidedByUser', 'settledByUser'])->latest();

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

        // Summary calculations directly via SQL before pagination
        $statsQuery = clone $query;
        $totalRevenue = (clone $statsQuery)->where('status', 'PAID')->sum('grand_total');
        $paidCount = (clone $statsQuery)->where('status', 'PAID')->count();
        $cashCount = (clone $statsQuery)->where('status', 'PAID')->where('payment_method', 'CASH')->count();
        $qrisCount = (clone $statsQuery)->where('status', 'PAID')->where('payment_method', 'QRIS')->count();
        $voidCount = (clone $statsQuery)->where('status', 'VOID')->count();

        // Profit & HPP calculation
        $paidOrderIds = (clone $statsQuery)->where('status', 'PAID')->pluck('id');
        $totalHpp = OrderItem::whereIn('order_id', $paidOrderIds)->sum(DB::raw('cost_price * qty'));
        $totalGrossProfit = max(0, $totalRevenue - $totalHpp);
        $profitMargin = ($totalRevenue > 0) ? round(($totalGrossProfit / $totalRevenue) * 100, 1) : 0;

        $orders = $query->paginate(25)->withQueryString();

        return view('admin.history', compact(
            'orders',
            'totalRevenue',
            'totalHpp',
            'totalGrossProfit',
            'profitMargin',
            'paidCount',
            'cashCount',
            'qrisCount',
            'voidCount'
        ));
    }

    public function voidOrder(Request $request, $orderId)
    {
        // 1. Authorization check: only Administrator can void
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            return back()->with('error', 'Akses ditolak! Pembatalan (VOID) transaksi hanya boleh dilakukan oleh Administrator.');
        }

        $order = Order::with('items.product')->where('order_id', $orderId)->firstOrFail();

        if (in_array($order->status, ['VOID', 'CANCELLED'])) {
            return back()->with('error', "Pesanan ini sudah berstatus {$order->status}.");
        }

        $request->validate([
            'void_reason' => 'required|string|max:500',
        ], [
            'void_reason.required' => 'Alasan pembatalan / void wajib diisi!',
        ]);

        DB::transaction(function () use ($order, $request) {
            // 1. Restock items that have manage_stock enabled
            foreach ($order->items as $item) {
                $product = $item->product ?? Product::find($item->product_id);
                if ($product && $product->manage_stock) {
                    $product->increment('stock', $item->qty);
                }
            }

            // 2. Rollback coupon count if any
            if ($order->coupon_code) {
                $coupon = Coupon::where('code', $order->coupon_code)->first();
                if ($coupon && $coupon->used_count > 0) {
                    $coupon->decrement('used_count');
                }
            }

            // 3. Rollback customer loyalty points if any
            if ($order->customer_id && $order->points_earned > 0) {
                \App\Models\Customer::where('id', $order->customer_id)->decrement('points', $order->points_earned);
            }
            if ($order->customer_id && $order->points_redeemed > 0) {
                \App\Models\Customer::where('id', $order->customer_id)->increment('points', $order->points_redeemed);
            }

            // 4. Mark order as VOID
            $order->update([
                'status' => 'VOID',
                'void_reason' => trim($request->void_reason),
                'void_by' => auth()->id(),
                'voided_at' => now(),
            ]);
        });

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

        // Profit & HPP calculation
        $paidOrderIds = $orders->where('status', 'PAID')->pluck('id');
        $totalHpp = OrderItem::whereIn('order_id', $paidOrderIds)->sum(DB::raw('cost_price * qty'));
        $totalGrossProfit = max(0, $totalNetRevenue - $totalHpp);

        // Best-Selling Items in this filtered batch
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
            'totalHpp',
            'totalGrossProfit',
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

    public function exportCsv(Request $request)
    {
        $query = Order::with(['items', 'user', 'customer'])->latest();

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->get();
        $filename = 'Laporan-Transaksi-' . date('Ymd-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            // UTF-8 BOM for Microsoft Excel compatibility
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                'No. Order',
                'Waktu Transaksi',
                'Kasir',
                'Pelanggan / Member',
                'Tipe Pesanan',
                'Metode Pembayaran',
                'Status',
                'Total Kotor (Rp)',
                'Diskon (Rp)',
                'Biaya Layanan (Rp)',
                'Pajak PPN (Rp)',
                'Kode Unik (Rp)',
                'Grand Total (Rp)',
                'Total HPP Modal (Rp)',
                'Laba Kotor / Profit (Rp)',
                'Rincian Menu',
                'Catatan / Alasan Void',
            ]);

            foreach ($orders as $order) {
                $totalHpp = $order->items->sum(function ($item) {
                    return $item->cost_price * $item->qty;
                });
                $grossProfit = ($order->status === 'PAID') ? ($order->grand_total - $totalHpp) : 0;

                $itemsSummary = $order->items->map(function ($item) {
                    return "{$item->qty}x {$item->product_name}";
                })->implode('; ');

                fputcsv($file, [
                    $order->order_id,
                    $order->created_at->format('Y-m-d H:i:s'),
                    $order->user ? $order->user->name : '-',
                    $order->customer ? "{$order->customer->name} ({$order->customer->phone})" : $order->customer_name,
                    $order->order_type ? str_replace('_', ' ', $order->order_type) : '-',
                    $order->payment_method,
                    $order->status,
                    $order->base_total,
                    $order->discount,
                    $order->service,
                    $order->tax,
                    $order->unique_code,
                    $order->grand_total,
                    $totalHpp,
                    $grossProfit,
                    $itemsSummary,
                    $order->void_reason ?: $order->notes,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

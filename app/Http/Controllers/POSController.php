<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Setting;

class POSController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->get();
        $products = Product::with('category')->where('is_active', true)->get();
        
        $taxSettings = [
            'enable_tax' => Setting::get('enable_tax', '0') == '1',
            'tax_rate' => floatval(Setting::get('tax_rate', '11')),
            'enable_service' => Setting::get('enable_service', '0') == '1',
            'service_rate' => floatval(Setting::get('service_rate', '5')),
        ];

        return view('pos.index', compact('categories', 'products', 'taxSettings'));
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'items' => 'required|string',
            'payment_method' => 'required|in:CASH,QRIS',
            'customer_name' => 'required|string|max:100',
            'cash_received' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'tax' => 'nullable|numeric|min:0',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'service' => 'nullable|numeric|min:0',
            'service_percent' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:500',
        ], [
            'customer_name.required' => 'Nama Pelanggan atau Nomor Meja wajib diisi!',
        ]);

        $items = json_decode($request->items, true);
        
        if (empty($items)) {
            return back()->with('error', 'Keranjang belanja masih kosong!');
        }

        $baseTotal = 0;
        foreach ($items as $item) {
            $baseTotal += ($item['price'] * $item['qty']);
        }

        $discountPercent = floatval($request->discount_percent ?? 0);
        $discountAmount = floatval($request->discount ?? ($baseTotal * ($discountPercent / 100)));
        $subtotalAfterDiscount = max(0, $baseTotal - $discountAmount);

        $servicePercent = floatval($request->service_percent ?? 0);
        $serviceAmount = floatval($request->service ?? ($subtotalAfterDiscount * ($servicePercent / 100)));

        $taxPercent = floatval($request->tax_percent ?? 0);
        $taxAmount = floatval($request->tax ?? (($subtotalAfterDiscount + $serviceAmount) * ($taxPercent / 100)));

        $paymentMethod = $request->payment_method;
        $uniqueCode = ($paymentMethod === 'QRIS') ? rand(1, 999) : 0;
        $grandTotal = $subtotalAfterDiscount + $serviceAmount + $taxAmount + $uniqueCode;
        
        $cashReceived = floatval($request->cash_received ?? $grandTotal);
        $cashChange = max(0, $cashReceived - $grandTotal);

        $orderId = 'ORD-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4));

        // 1. Save Order
        $order = Order::create([
            'user_id' => Auth::id(),
            'order_id' => $orderId,
            'customer_name' => trim($request->customer_name),
            'base_total' => $baseTotal,
            'discount' => $discountAmount,
            'discount_percent' => $discountPercent,
            'service' => $serviceAmount,
            'service_percent' => $servicePercent,
            'tax' => $taxAmount,
            'tax_percent' => $taxPercent,
            'unique_code' => $uniqueCode,
            'grand_total' => $grandTotal,
            'cash_received' => $cashReceived,
            'cash_change' => $cashChange,
            'notes' => $request->notes,
            'status' => ($paymentMethod === 'CASH') ? 'PAID' : 'PENDING',
            'payment_method' => $paymentMethod,
            'settlement_type' => ($paymentMethod === 'CASH') ? 'MANUAL_CASHIER' : 'AUTOMATIC_WEBHOOK',
        ]);

        // 2. Save Items & Deduct Stock only if manage_stock is true
        foreach ($items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['id'],
                'product_name' => $item['name'],
                'qty' => $item['qty'],
                'price' => $item['price'],
                'subtotal' => $item['price'] * $item['qty'],
            ]);

            $product = Product::find($item['id']);
            if ($product && $product->manage_stock) {
                $product->stock = max(0, $product->stock - $item['qty']);
                $product->save();
            }
        }

        // 3. Process QRIS flow if QRIS selected
        if ($paymentMethod === 'QRIS') {
            $nodeJsUrl = env('PAYMENT_GATEWAY_URL', 'http://localhost:3000/api/v1/qris/generate');
            $apiKey = env('PAYMENT_GATEWAY_API_KEY', 'secret_key_hp_123');
            $expiresInMinutes = (int) Setting::get('qris_expires_minutes', '15');

            try {
                $response = Http::withHeaders([
                    'x-api-key' => $apiKey
                ])->timeout(8)->post($nodeJsUrl, [
                    'amount' => (int) $grandTotal,
                    'invoice_id' => (string) $orderId,
                    'expires_in_minutes' => (int) $expiresInMinutes,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $qrisBase64 = $data['data']['qris_base64'] ?? null;

                    return view('pos.checkout', [
                        'order' => $order,
                        'qris_image' => $qrisBase64
                    ]);
                }
            } catch (\Exception $e) {
                return redirect()->route('pos.index')->with('error', 'Layanan QRIS Gateway belum aktif. Anda bisa menggunakan pembayaran Tunai (Cash) sementara.');
            }

            return redirect()->route('pos.index')->with('error', 'Gagal membuat QRIS dari Gateway. Silakan coba pembayaran Tunai.');
        }

        // Cash flow: 100% direct instant receipt!
        return redirect()->route('pos.receipt', ['orderId' => $orderId])->with('success', 'Transaksi Tunai Berhasil!');
    }

    public function manualSettle(Request $request, $orderId)
    {
        $order = Order::where('order_id', $orderId)->firstOrFail();

        if ($order->status === 'PAID') {
            return redirect()->route('pos.receipt', ['orderId' => $orderId]);
        }

        $request->validate([
            'payment_proof' => 'nullable|image|max:51200',
            'payment_proof_base64' => 'nullable|string',
            'notes' => 'nullable|string|max:500',
        ]);

        $proofPath = null;

        // 1. Handle file upload (from input file / camera capture)
        if ($request->hasFile('payment_proof')) {
            $proofPath = $request->file('payment_proof')->store('proofs', 'public');
        }
        // 2. Handle base64 snapshot from web camera
        elseif (!empty($request->payment_proof_base64) && str_starts_with($request->payment_proof_base64, 'data:image')) {
            $imageParts = explode(';base64,', $request->payment_proof_base64);
            if (count($imageParts) === 2) {
                $imageTypeAux = explode('image/', $imageParts[0]);
                $imageType = $imageTypeAux[1] ?? 'png';
                $imageBase64 = base64_decode($imageParts[1]);
                $filename = 'proofs/' . uniqid() . '.' . $imageType;
                Storage::disk('public')->put($filename, $imageBase64);
                $proofPath = $filename;
            }
        }

        $order->status = 'PAID';
        $order->settlement_type = 'MANUAL_CASHIER';
        $order->settled_by = Auth::id();
        if ($proofPath) {
            $order->payment_proof = $proofPath;
        }
        if ($request->filled('notes')) {
            $order->notes = $order->notes ? ($order->notes . ' | ' . $request->notes) : $request->notes;
        }
        $order->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'redirect_url' => route('pos.receipt', ['orderId' => $orderId]),
            ]);
        }

        return redirect()->route('pos.receipt', ['orderId' => $orderId])->with('success', 'Pembayaran QRIS berhasil dikonfirmasi secara manual!');
    }

    public function webhookCallback(Request $request)
    {
        $backendApiKey = $request->header('x-api-key');
        $expectedKey = env('BACKEND_API_KEY', 'secret_backend_123');

        if ($backendApiKey !== $expectedKey) {
            return response()->json(['message' => 'Unauthorized key'], 401);
        }

        $payload = $request->all();
        
        if (isset($payload['status']) && $payload['status'] === 'PAID') {
            $order = Order::where('order_id', $payload['invoice_id_backend'])->first();

            if ($order && $order->status === 'PENDING') {
                $order->status = 'PAID';
                $order->settlement_type = 'AUTOMATIC_WEBHOOK';
                $order->save();
            }
        }

        return response()->json(['status' => 'success']);
    }

    public function checkStatus($orderId)
    {
        $order = Order::where('order_id', $orderId)->first();
        if (!$order) {
            return response()->json(['status' => 'NOT_FOUND'], 404);
        }
        return response()->json(['status' => $order->status]);
    }

    public function printReceipt($orderId)
    {
        $order = Order::with(['items', 'user'])->where('order_id', $orderId)->firstOrFail();
        return view('pos.receipt', compact('order'));
    }
}

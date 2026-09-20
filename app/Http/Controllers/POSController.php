<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\CashShift;

class POSController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->get();
        $products = Product::with('category')->where('is_active', true)->get();
        $activeCoupons = Coupon::with('category')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->get();
        
        $featureSettings = [
            'enable_shifts' => Setting::get('enable_shifts', '1') == '1',
            'enable_petty_cash' => Setting::get('enable_petty_cash', '1') == '1',
            'enable_points' => Setting::get('enable_points', '1') == '1',
            'enable_split_payment' => Setting::get('enable_split_payment', '1') == '1',
        ];

        $activeShift = null;
        if ($featureSettings['enable_shifts']) {
            $activeShift = CashShift::where('user_id', Auth::id())->where('status', 'OPEN')->first();
        }

        $customers = Customer::orderBy('name')->take(50)->get();

        $taxSettings = [
            'enable_tax' => Setting::get('enable_tax', '0') == '1',
            'tax_rate' => floatval(Setting::get('tax_rate', '11')),
            'enable_service' => Setting::get('enable_service', '0') == '1',
            'service_rate' => floatval(Setting::get('service_rate', '5')),
            'enable_order_types' => Setting::get('enable_order_types', '1') == '1',
            'service_charge_on_takeaway' => Setting::get('service_charge_on_takeaway', '0') == '1',
            'tax_on_takeaway' => Setting::get('tax_on_takeaway', '1') == '1',
            'enable_kitchen_receipt' => Setting::get('enable_kitchen_receipt', '1') == '1',
        ];

        return view('pos.index', compact('categories', 'products', 'taxSettings', 'featureSettings', 'activeCoupons', 'activeShift', 'customers'));
    }

    public function validateCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $code = strtoupper(trim($request->code));
        $coupon = Coupon::with('category')->where('code', $code)->first();

        if (!$coupon) {
            return response()->json([
                'valid' => false,
                'message' => "Kode kupon \"{$code}\" tidak ditemukan atau sudah tidak berlaku!"
            ], 404);
        }

        $items = [];
        if ($request->filled('items')) {
            $items = is_array($request->items) ? $request->items : (json_decode($request->items, true) ?? []);
        }

        $result = $coupon->calculateDiscount($items, floatval($request->subtotal));

        if (!$result['valid']) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'items' => 'required|string',
            'payment_method' => 'required|in:CASH,QRIS,TRANSFER,DEBIT',
            'order_type' => 'nullable|in:DINE_IN,TAKE_AWAY',
            'customer_name' => 'required|string|max:100',
            'customer_id' => 'nullable|exists:customers,id',
            'cash_received' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'coupon_code' => 'nullable|string|max:50',
            'tax' => 'nullable|numeric|min:0',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'service' => 'nullable|numeric|min:0',
            'service_percent' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:500',
        ], [
            'customer_name.required' => 'Nama Pelanggan atau Nomor Meja wajib diisi!',
        ]);

        $rawItems = json_decode($request->items, true);
        
        if (empty($rawItems) || !is_array($rawItems)) {
            return back()->with('error', 'Keranjang belanja masih kosong!');
        }

        // 1. Fetch real products from DB to prevent client price tampering and validate stock
        $productIds = array_filter(array_column($rawItems, 'id'));
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $reconstructedItems = [];
        $baseTotal = 0;

        foreach ($rawItems as $rawItem) {
            $productId = $rawItem['id'] ?? null;
            $qty = intval($rawItem['qty'] ?? 0);

            if ($qty <= 0) {
                return back()->with('error', 'Jumlah produk harus lebih dari 0.');
            }

            $product = $products->get($productId);
            if (!$product || !$product->is_active) {
                return back()->with('error', 'Produk "' . ($rawItem['name'] ?? 'Pilihan') . '" tidak ditemukan atau sedang tidak aktif.');
            }

            // Overselling protection: check available stock
            if ($product->manage_stock && $product->stock < $qty) {
                return back()->with('error', "Stok untuk produk \"{$product->name}\" tidak mencukupi! Tersedia: {$product->stock}, diminta: {$qty}.");
            }

            // Real server-side price calculation
            $serverPrice = floatval($product->price);
            $costPrice = floatval($product->cost_price ?? 0);
            $itemSubtotal = $serverPrice * $qty;
            $baseTotal += $itemSubtotal;

            $reconstructedItems[] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $serverPrice,
                'cost_price' => $costPrice,
                'qty' => $qty,
                'subtotal' => $itemSubtotal,
                'notes' => !empty($rawItem['notes']) ? trim($rawItem['notes']) : null,
                'product_model' => $product,
            ];
        }

        // 2. Server-side discount & coupon calculation
        $discountAmount = 0;
        $discountPercent = 0;
        $couponCode = null;

        if ($request->filled('coupon_code')) {
            $coupon = Coupon::with('category')->where('code', strtoupper(trim($request->coupon_code)))->first();
            if (!$coupon) {
                return back()->with('error', 'Kode kupon promo tidak ditemukan.');
            }

            $couponCalc = $coupon->calculateDiscount($reconstructedItems, $baseTotal);
            if (!$couponCalc['valid']) {
                return back()->with('error', 'Kupon promo tidak valid: ' . $couponCalc['message']);
            }

            $discountAmount = floatval($couponCalc['discount_amount']);
            $discountPercent = floatval($couponCalc['discount_percent']);
            $couponCode = $coupon->code;
        } elseif ($request->filled('discount_percent') && floatval($request->discount_percent) > 0) {
            $discountPercent = min(100, max(0, floatval($request->discount_percent)));
            $discountAmount = round($baseTotal * ($discountPercent / 100), 2);
        } elseif ($request->filled('discount') && floatval($request->discount) > 0) {
            $discountAmount = min($baseTotal, max(0, floatval($request->discount)));
            $discountPercent = ($baseTotal > 0) ? round(($discountAmount / $baseTotal) * 100, 2) : 0;
        }

        $subtotalAfterDiscount = max(0, $baseTotal - $discountAmount);

        // 2b. Loyalty Points Redemption (1 point = Rp 1.000 discount)
        $enablePoints = Setting::get('enable_points', '1') == '1';
        $pointsRedeemed = 0;
        $pointsDiscount = 0;
        $customerId = $request->filled('customer_id') ? $request->customer_id : null;
        $customer = ($enablePoints && $customerId) ? Customer::find($customerId) : null;

        if ($enablePoints && $customer && $request->filled('points_to_redeem') && intval($request->points_to_redeem) > 0) {
            $requestedPoints = intval($request->points_to_redeem);
            $availablePoints = $customer->points;
            $pointsRedeemed = min($availablePoints, $requestedPoints);
            $maxDiscountPossible = $subtotalAfterDiscount;
            $pointsDiscount = min($maxDiscountPossible, $pointsRedeemed * 1000);
            $pointsRedeemed = (int) ceil($pointsDiscount / 1000);
            $subtotalAfterDiscount = max(0, $subtotalAfterDiscount - $pointsDiscount);
        }

        // 3. Server-side Service & Tax calculation
        $enableOrderTypes = Setting::get('enable_order_types', '1') == '1';
        $orderType = $enableOrderTypes ? ($request->order_type ?: 'DINE_IN') : null;

        $enableService = Setting::get('enable_service', '0') == '1';
        $serviceRate = $enableService ? floatval(Setting::get('service_rate', '5')) : 0;
        if ($orderType === 'TAKE_AWAY' && Setting::get('service_charge_on_takeaway', '0') != '1') {
            $serviceRate = 0;
        }
        $serviceAmount = round($subtotalAfterDiscount * ($serviceRate / 100), 2);

        $enableTax = Setting::get('enable_tax', '0') == '1';
        $taxRate = $enableTax ? floatval(Setting::get('tax_rate', '11')) : 0;
        if ($orderType === 'TAKE_AWAY' && Setting::get('tax_on_takeaway', '1') != '1') {
            $taxRate = 0;
        }
        $taxAmount = round(($subtotalAfterDiscount + $serviceAmount) * ($taxRate / 100), 2);

        $paymentMethod = $request->payment_method;
        $enableSplitPayment = Setting::get('enable_split_payment', '1') == '1';
        if ($paymentMethod === 'SPLIT' && !$enableSplitPayment) {
            return back()->with('error', 'Fitur Split Payment sedang dinonaktifkan oleh toko.');
        }

        $isSplitPayment = ($paymentMethod === 'SPLIT');
        $paymentDetails = null;
        $splitCash = 0;
        $splitNonCash = 0;

        $uniqueCode = ($paymentMethod === 'QRIS') ? rand(1, 99) : 0;
        $grandTotal = $subtotalAfterDiscount + $serviceAmount + $taxAmount + $uniqueCode;

        if ($isSplitPayment) {
            $request->validate([
                'split_method_1' => 'required|string',
                'split_amount_1' => 'required|numeric|min:1',
                'split_method_2' => 'required|string',
                'split_amount_2' => 'required|numeric|min:1',
            ]);

            $splitAmt1 = floatval($request->split_amount_1);
            $splitAmt2 = floatval($request->split_amount_2);

            if (round($splitAmt1 + $splitAmt2, 2) != round($grandTotal, 2)) {
                return back()->with('error', 'Jumlah pembayaran terpisah (Rp ' . number_format($splitAmt1 + $splitAmt2, 0, ',', '.') . ') harus sama persis dengan total tagihan (Rp ' . number_format($grandTotal, 0, ',', '.') . ')!');
            }

            $paymentDetails = [
                ['method' => $request->split_method_1, 'amount' => $splitAmt1],
                ['method' => $request->split_method_2, 'amount' => $splitAmt2],
            ];

            if ($request->split_method_1 === 'CASH') $splitCash += $splitAmt1;
            else $splitNonCash += $splitAmt1;

            if ($request->split_method_2 === 'CASH') $splitCash += $splitAmt2;
            else $splitNonCash += $splitAmt2;
        }

        // Cash / Card / Transfer handling
        $cashRequired = $isSplitPayment ? $splitCash : (($paymentMethod === 'CASH') ? $grandTotal : 0);
        $cashReceived = ($paymentMethod === 'CASH' || ($isSplitPayment && $splitCash > 0)) ? floatval($request->cash_received ?? $cashRequired) : 0;
        
        if ($cashRequired > 0 && $cashReceived < $cashRequired) {
            return back()->with('error', 'Uang tunai yang diterima (Rp ' . number_format($cashReceived, 0, ',', '.') . ') kurang dari bagian tunai tagihan (Rp ' . number_format($cashRequired, 0, ',', '.') . ')!');
        }
        $cashChange = ($cashRequired > 0) ? max(0, $cashReceived - $cashRequired) : 0;

        // Member CRM & Loyalty points (1 point per Rp 10.000)
        $pointsEarned = ($enablePoints && $customerId) ? intval(floor($grandTotal / 10000)) : 0;

        $orderId = 'ORD-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4));

        // 4. Atomic Execution inside DB Transaction
        $order = null;
        $qrisBase64 = null;

        $isInstantPaid = in_array($paymentMethod, ['CASH', 'TRANSFER', 'DEBIT', 'SPLIT']);

        try {
            DB::transaction(function () use (
                &$order, &$qrisBase64, $request, $orderId, $orderType, $baseTotal,
                $discountAmount, $discountPercent, $couponCode, $pointsRedeemed, $pointsDiscount,
                $serviceAmount, $serviceRate, $taxAmount, $taxRate, $uniqueCode, $grandTotal,
                $cashReceived, $cashChange, $paymentMethod, $isSplitPayment, $paymentDetails,
                $splitCash, $splitNonCash, $reconstructedItems, $customerId, $pointsEarned, $isInstantPaid
            ) {
                // Increment coupon used count inside transaction
                if ($couponCode) {
                    Coupon::where('code', $couponCode)->increment('used_count');
                }

                // 4a. Create Order
                $order = Order::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $customerId,
                    'order_id' => $orderId,
                    'customer_name' => trim($request->customer_name),
                    'order_type' => $orderType,
                    'base_total' => $baseTotal,
                    'discount' => $discountAmount,
                    'discount_percent' => $discountPercent,
                    'points_redeemed' => $pointsRedeemed,
                    'points_discount' => $pointsDiscount,
                    'points_earned' => $pointsEarned,
                    'coupon_code' => $couponCode,
                    'service' => $serviceAmount,
                    'service_percent' => $serviceRate,
                    'tax' => $taxAmount,
                    'tax_percent' => $taxRate,
                    'unique_code' => $uniqueCode,
                    'grand_total' => $grandTotal,
                    'cash_received' => $cashReceived,
                    'cash_change' => $cashChange,
                    'notes' => $request->notes,
                    'status' => $isInstantPaid ? 'PAID' : 'PENDING',
                    'payment_method' => $paymentMethod,
                    'is_split_payment' => $isSplitPayment,
                    'payment_details' => $paymentDetails,
                    'kitchen_status' => 'PENDING',
                    'kitchen_updated_at' => now(),
                    'settlement_type' => $isInstantPaid ? 'MANUAL_CASHIER' : 'AUTOMATIC_WEBHOOK',
                ]);

                // 4b. Create OrderItems with cost_price & Deduct Stock
                foreach ($reconstructedItems as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['id'],
                        'product_name' => $item['name'],
                        'qty' => $item['qty'],
                        'price' => $item['price'],
                        'cost_price' => $item['cost_price'],
                        'subtotal' => $item['subtotal'],
                        'notes' => $item['notes'],
                    ]);

                    /** @var Product $prod */
                    $prod = $item['product_model'];
                    if ($prod->manage_stock) {
                        $prod->decrement('stock', $item['qty']);
                    }
                }

                // 4c. Update Member Points (Deduct redeemed, Add earned if instant paid)
                if ($customerId && $pointsRedeemed > 0) {
                    Customer::where('id', $customerId)->decrement('points', $pointsRedeemed);
                }
                if ($customerId && $pointsEarned > 0 && $isInstantPaid) {
                    Customer::where('id', $customerId)->increment('points', $pointsEarned);
                }

                // 4d. Update Cashier Shift if active and enabled
                if (Setting::get('enable_shifts', '1') == '1') {
                    $activeShift = CashShift::where('user_id', Auth::id())->where('status', 'OPEN')->first();
                    if ($activeShift && $isInstantPaid) {
                        if ($paymentMethod === 'CASH') {
                            $activeShift->increment('cash_sales', $grandTotal);
                        } elseif ($paymentMethod === 'SPLIT') {
                            if ($splitCash > 0) $activeShift->increment('cash_sales', $splitCash);
                            if ($splitNonCash > 0) $activeShift->increment('non_cash_sales', $splitNonCash);
                        } else {
                            $activeShift->increment('non_cash_sales', $grandTotal);
                        }
                    }
                }

                // 4e. Process QRIS Gateway if QRIS selected (Rollback everything if gateway fails)
                if ($paymentMethod === 'QRIS') {
                    $nodeJsUrl = Setting::get('payment_gateway_url', env('PAYMENT_GATEWAY_URL', 'http://localhost:3000/api/v1/qris/generate'));
                    $apiKey = Setting::get('payment_gateway_api_key', env('PAYMENT_GATEWAY_API_KEY', 'secret_key_hp_123'));
                    $expiresInMinutes = (int) Setting::get('qris_expires_minutes', '15');

                    try {
                        $response = Http::withHeaders([
                            'x-api-key' => $apiKey
                        ])->timeout(8)->post($nodeJsUrl, [
                            'amount' => (int) $grandTotal,
                            'invoice_id' => (string) $orderId,
                            'expires_in_minutes' => (int) $expiresInMinutes,
                        ]);

                        if (!$response->successful()) {
                            throw new \Exception('Layanan QRIS Gateway tidak merespons atau mengembalikan error HTTP ' . $response->status());
                        }

                        $data = $response->json();
                        $qrisBase64 = $data['data']['qris_base64'] ?? null;
                        if (!$qrisBase64) {
                            throw new \Exception('Respons gambar QRIS tidak valid dari Gateway.');
                        }
                    } catch (\Exception $e) {
                        throw new \Exception('Gagal menghubungi Gateway QRIS: ' . $e->getMessage() . '. Silakan gunakan pembayaran Tunai (Cash) sementara.');
                    }
                }
            });
        } catch (\Exception $e) {
            return redirect()->route('pos.index')->with('error', $e->getMessage());
        }

        // Return QRIS Waiting Page if QRIS
        if ($paymentMethod === 'QRIS') {
            return view('pos.checkout', [
                'order' => $order,
                'qris_image' => $qrisBase64
            ]);
        }

        // Cash, Transfer, Debit, Split flow: direct instant receipt!
        $msg = match ($paymentMethod) {
            'CASH' => 'Transaksi Tunai Berhasil!',
            'DEBIT' => 'Transaksi Kartu/EDC Berhasil!',
            'TRANSFER' => 'Transaksi Transfer Bank Berhasil!',
            'SPLIT' => 'Transaksi Split Payment (Pisah Bayar) Berhasil!',
            default => 'Transaksi Berhasil!'
        };
        return redirect()->route('pos.receipt', ['orderId' => $orderId])->with('success', $msg);
    }

    public function cancelPendingOrder(Request $request, $orderId)
    {
        $order = Order::with('items.product')->where('order_id', $orderId)->firstOrFail();

        if ($order->status !== 'PENDING') {
            return redirect()->route('pos.index')->with('error', "Pesanan #{$order->order_id} berstatus {$order->status} dan tidak dapat dibatalkan melalui alur ini.");
        }

        DB::transaction(function () use ($order) {
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

            // 3. Rollback redeemed customer points if any
            if ($order->customer_id && $order->points_redeemed > 0) {
                Customer::where('id', $order->customer_id)->increment('points', $order->points_redeemed);
            }

            // 4. Update order status to CANCELLED
            $order->update([
                'status' => 'CANCELLED',
                'notes' => ($order->notes ? $order->notes . ' | ' : '') . 'Dibatalkan oleh Kasir saat menunggu QRIS',
            ]);
        });

        return redirect()->route('pos.index')->with('success', "Pesanan QRIS #{$order->order_id} berhasil dibatalkan dan stok produk telah dikembalikan.");
    }

    public function manualSettle(Request $request, $orderId)
    {
        $order = Order::where('order_id', $orderId)->firstOrFail();

        if ($order->status === 'PAID') {
            return redirect()->route('pos.receipt', ['orderId' => $orderId]);
        }

        if ($order->status !== 'PENDING') {
            return redirect()->route('pos.index')->with('error', "Pesanan #{$orderId} berstatus {$order->status} dan tidak dapat diselesaikan.");
        }

        $request->validate([
            'payment_proof' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
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

        // Award loyalty points if customer attached
        if ($order->customer_id && $order->points_earned > 0) {
            Customer::where('id', $order->customer_id)->increment('points', $order->points_earned);
        }

        // Add non-cash sales to cashier's active shift
        $activeShift = CashShift::where('user_id', Auth::id())->where('status', 'OPEN')->first();
        if ($activeShift) {
            $activeShift->increment('non_cash_sales', $order->grand_total);
        }

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
        $expectedKey = Setting::get('backend_api_key', env('BACKEND_API_KEY', 'secret_backend_123'));

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

                // Award points to member
                if ($order->customer_id && $order->points_earned > 0) {
                    Customer::where('id', $order->customer_id)->increment('points', $order->points_earned);
                }

                // Add to shift
                $activeShift = CashShift::where('user_id', $order->user_id)->where('status', 'OPEN')->first();
                if ($activeShift) {
                    $activeShift->increment('non_cash_sales', $order->grand_total);
                }
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
        $enableKitchenReceipt = Setting::get('enable_kitchen_receipt', '1') == '1';
        return view('pos.receipt', compact('order', 'enableKitchenReceipt'));
    }

    public function kitchenReceipt($orderId)
    {
        $order = Order::with(['items', 'user'])->where('order_id', $orderId)->firstOrFail();
        return view('pos.kitchen_receipt', compact('order'));
    }
}

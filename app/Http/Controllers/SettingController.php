<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'store_name' => Setting::get('store_name', 'POSPRO CAFE & RESTO'),
            'store_address' => Setting::get('store_address', 'Jl. Sudirman No. 123, Jakarta Selatan'),
            'store_phone' => Setting::get('store_phone', '0812-3456-7890'),
            'receipt_footer' => Setting::get('receipt_footer', "Terima Kasih Atas Kunjungan Anda!\nBarang yang sudah dibeli tidak dapat ditukar"),
            'enable_tax' => Setting::get('enable_tax', '0'),
            'tax_rate' => Setting::get('tax_rate', '11'),
            'enable_service' => Setting::get('enable_service', '0'),
            'service_rate' => Setting::get('service_rate', '5'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'store_name' => 'required|string|max:255',
            'store_address' => 'nullable|string|max:500',
            'store_phone' => 'nullable|string|max:50',
            'receipt_footer' => 'nullable|string|max:500',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'service_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        Setting::set('store_name', $request->store_name);
        Setting::set('store_address', $request->store_address);
        Setting::set('store_phone', $request->store_phone);
        Setting::set('receipt_footer', $request->receipt_footer);

        // Tax & Service configuration
        Setting::set('enable_tax', $request->boolean('enable_tax') ? '1' : '0');
        Setting::set('tax_rate', $request->filled('tax_rate') ? $request->tax_rate : '11');
        Setting::set('enable_service', $request->boolean('enable_service') ? '1' : '0');
        Setting::set('service_rate', $request->filled('service_rate') ? $request->service_rate : '5');

        return redirect()->route('admin.settings')->with('success', 'Pengaturan toko, pajak PPN, dan biaya layanan berhasil diperbarui!');
    }
}

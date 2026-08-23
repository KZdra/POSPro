<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan - {{ $store['name'] }}</title>
    <style>
        @page {
            margin: 1.2cm 1.2cm 1.5cm 1.2cm;
            size: a4 portrait;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        .header-table {
            width: 100%;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .store-title {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .store-sub {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
        }

        .report-title {
            font-size: 14px;
            font-weight: bold;
            color: #2563eb;
            text-align: right;
            text-transform: uppercase;
        }

        .report-period {
            font-size: 9px;
            color: #475569;
            text-align: right;
            margin-top: 2px;
            font-weight: 500;
        }

        /* KPI Matrix Cards */
        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
            margin-bottom: 16px;
        }

        .kpi-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 10px;
            text-align: center;
        }

        .kpi-label {
            font-size: 8px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .kpi-value {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 3px;
        }

        .text-blue { color: #2563eb; }
        .text-emerald { color: #059669; }
        .text-amber { color: #d97706; }
        .text-purple { color: #7c3aed; }

        /* Section Headings */
        .section-title {
            font-size: 10px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
            border-left: 3px solid #2563eb;
            padding-left: 6px;
        }

        /* Tables */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        table.data-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: bold;
            font-size: 8.5px;
            text-transform: uppercase;
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            text-align: left;
        }

        table.data-table td {
            padding: 5px 8px;
            border: 1px solid #e2e8f0;
            font-size: 9px;
            color: #334155;
        }

        table.data-table tr:nth-child(even) {
            background-color: #fafbfc;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .font-mono { font-family: monospace; font-size: 8.5px; }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-paid { background-color: #d1fae5; color: #065f46; }
        .badge-pending { background-color: #fef3c7; color: #92400e; }
        .badge-cash { background-color: #dbeafe; color: #1e40af; }
        .badge-qris { background-color: #ede9fe; color: #5b21b6; }

        /* Signatures */
        .signature-table {
            width: 100%;
            margin-top: 30px;
            page-break-inside: avoid;
        }

        .signature-box {
            width: 35%;
            text-align: center;
        }

        .signature-space {
            height: 50px;
        }

        .signature-line {
            border-top: 1px solid #0f172a;
            font-weight: bold;
            padding-top: 4px;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td style="vertical-align: top; width: 55%;">
                <div class="store-title">{{ $store['name'] }}</div>
                <div class="store-sub">{{ $store['address'] }}</div>
                <div class="store-sub">Telp / WA: {{ $store['phone'] }}</div>
            </td>
            <td style="vertical-align: top; width: 45%; text-align: right;">
                <div class="report-title">Rekapitulasi Penjualan</div>
                <div class="report-period">
                    Periode: 
                    @if($startDate && $endDate)
                        <strong>{{ date('d/m/Y', strtotime($startDate)) }} s/d {{ date('d/m/Y', strtotime($endDate)) }}</strong>
                    @elseif($startDate)
                        <strong>Mulai {{ date('d/m/Y', strtotime($startDate)) }}</strong>
                    @elseif($endDate)
                        <strong>Sampai {{ date('d/m/Y', strtotime($endDate)) }}</strong>
                    @else
                        <strong>Semua Waktu</strong>
                    @endif
                </div>
                <div class="store-sub" style="margin-top: 2px;">
                    Dicetak: {{ date('d M Y, H:i') }} WIB
                </div>
            </td>
        </tr>
    </table>

    <!-- KPI Summary Matrix -->
    <table class="kpi-table">
        <tr>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Total Omset Bersih</div>
                <div class="kpi-value text-blue">Rp {{ number_format($totalNetRevenue, 0, ',', '.') }}</div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Transaksi Lunas</div>
                <div class="kpi-value text-emerald">{{ $paidOrdersCount }} Pesanan</div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Tunai (Cash)</div>
                <div class="kpi-value text-amber">Rp {{ number_format($cashTotal, 0, ',', '.') }} <span style="font-size: 8px; font-weight: normal;">({{ $cashCount }})</span></div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">QRIS Gateway</div>
                <div class="kpi-value text-purple">Rp {{ number_format($qrisTotal, 0, ',', '.') }} <span style="font-size: 8px; font-weight: normal;">({{ $qrisCount }})</span></div>
            </td>
        </tr>
        <tr>
            <td class="kpi-card" style="width: 25%; background-color: #ffffff;">
                <div class="kpi-label">Subtotal Menu</div>
                <div class="kpi-value" style="font-size: 11px;">Rp {{ number_format($totalGross, 0, ',', '.') }}</div>
            </td>
            <td class="kpi-card" style="width: 25%; background-color: #ffffff;">
                <div class="kpi-label">Total Diskon</div>
                <div class="kpi-value" style="font-size: 11px; color: #dc2626;">-Rp {{ number_format($totalDiscount, 0, ',', '.') }}</div>
            </td>
            <td class="kpi-card" style="width: 25%; background-color: #ffffff;">
                <div class="kpi-label">Biaya Layanan</div>
                <div class="kpi-value" style="font-size: 11px;">+Rp {{ number_format($totalService, 0, ',', '.') }}</div>
            </td>
            <td class="kpi-card" style="width: 25%; background-color: #ffffff;">
                <div class="kpi-label">Pajak (PPN)</div>
                <div class="kpi-value" style="font-size: 11px;">+Rp {{ number_format($totalTax, 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <!-- Top 5 Best Selling Items -->
    @if($topProducts->count() > 0)
        <div class="section-title">5 Menu / Produk Terlaris</div>
        <table class="data-table" style="margin-bottom: 18px;">
            <thead>
                <tr>
                    <th style="width: 5%;" class="text-center">No</th>
                    <th style="width: 55%;">Nama Produk / Menu</th>
                    <th style="width: 20%;" class="text-center">Qty Terjual</th>
                    <th style="width: 20%;" class="text-right">Total Nominal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topProducts as $idx => $prod)
                    <tr>
                        <td class="text-center font-bold">{{ $idx + 1 }}</td>
                        <td class="font-bold">{{ $prod->product_name }}</td>
                        <td class="text-center font-bold text-blue">{{ $prod->total_qty }} Porsi / Unit</td>
                        <td class="text-right font-bold">Rp {{ number_format($prod->total_amount, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Transaction List Table -->
    <div class="section-title">Rincian Transaksi Penjualan ({{ $orders->count() }} Data)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;" class="text-center">No</th>
                <th style="width: 14%;">Waktu</th>
                <th style="width: 16%;">No. Order</th>
                <th style="width: 16%;">Pelanggan / Meja</th>
                <th style="width: 12%;">Kasir</th>
                <th style="width: 10%;" class="text-center">Metode</th>
                <th style="width: 14%;" class="text-right">Total Bayar</th>
                <th style="width: 14%;" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $i => $order)
                <tr>
                    <td class="text-center font-mono">{{ $i + 1 }}</td>
                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    <td class="font-mono font-bold">{{ $order->order_id }}</td>
                    <td class="font-bold">{{ $order->customer_name }}</td>
                    <td>{{ $order->user ? $order->user->name : '-' }}</td>
                    <td class="text-center">
                        <span class="badge {{ $order->payment_method === 'QRIS' ? 'badge-qris' : 'badge-cash' }}">
                            {{ $order->payment_method }}
                        </span>
                    </td>
                    <td class="text-right font-bold">
                        Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $order->status === 'PAID' ? 'badge-paid' : 'badge-pending' }}">
                            {{ $order->status }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 16px; color: #94a3b8;">
                        Tidak ada transaksi ditemukan pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($orders->count() > 0)
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: bold;">
                    <td colspan="6" class="text-right" style="padding: 7px 8px; font-size: 10px;">
                        TOTAL AKUMULASI (LUNAS):
                    </td>
                    <td class="text-right text-blue" style="padding: 7px 8px; font-size: 11px;">
                        Rp {{ number_format($totalNetRevenue, 0, ',', '.') }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- Signature Section -->
    <table class="signature-table">
        <tr>
            <td class="signature-box" style="float: left;">
                <div>Dibuat Oleh (Kasir / Petugas),</div>
                <div class="signature-space"></div>
                <div class="signature-line">( {{ Auth::check() ? Auth::user()->name : 'Petugas Kasir' }} )</div>
            </td>
            <td style="width: 30%;"></td>
            <td class="signature-box" style="float: right;">
                <div>Mengetahui (Owner / Manager),</div>
                <div class="signature-space"></div>
                <div class="signature-line">( ............................................ )</div>
            </td>
        </tr>
    </table>

</body>
</html>

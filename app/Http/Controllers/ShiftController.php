<?php

namespace App\Http\Controllers;

use App\Models\CashShift;
use App\Models\CashMovement;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = CashShift::with(['user', 'movements'])->latest()->paginate(20);
        $activeShift = CashShift::with(['user', 'movements'])->where('user_id', Auth::id())->where('status', 'OPEN')->first();
        return view('admin.shifts.index', compact('shifts', 'activeShift'));
    }

    public function current()
    {
        $shift = CashShift::with('movements')->where('user_id', Auth::id())->where('status', 'OPEN')->first();
        
        if (!$shift) {
            return response()->json(['active' => false, 'shift' => null]);
        }

        // Live calculation of current shift sales
        $cashSales = Order::where('user_id', Auth::id())
            ->where('created_at', '>=', $shift->opened_at)
            ->where('status', 'PAID')
            ->where('payment_method', 'CASH')
            ->sum('grand_total');

        $nonCashSales = Order::where('user_id', Auth::id())
            ->where('created_at', '>=', $shift->opened_at)
            ->where('status', 'PAID')
            ->where('payment_method', '!=', 'CASH')
            ->sum('grand_total');

        $totalCashIn = $shift->totalCashIn();
        $totalCashOut = $shift->totalCashOut();
        $expectedCash = floatval($shift->opening_cash) + floatval($cashSales) + $totalCashIn - $totalCashOut;

        return response()->json([
            'active' => true,
            'shift' => $shift,
            'cash_sales' => $cashSales,
            'non_cash_sales' => $nonCashSales,
            'total_cash_in' => $totalCashIn,
            'total_cash_out' => $totalCashOut,
            'expected_cash' => $expectedCash,
            'movements' => $shift->movements,
        ]);
    }

    public function addCashMovement(Request $request)
    {
        $request->validate([
            'type' => 'required|in:CASH_IN,CASH_OUT',
            'amount' => 'required|numeric|min:1',
            'reason' => 'required|string|max:255',
        ]);

        $activeShift = CashShift::where('user_id', Auth::id())->where('status', 'OPEN')->first();
        if (!$activeShift) {
            return back()->with('error', 'Tidak ada shift kasir yang sedang aktif.');
        }

        $movement = CashMovement::create([
            'cash_shift_id' => $activeShift->id,
            'user_id' => Auth::id(),
            'type' => $request->type,
            'amount' => floatval($request->amount),
            'reason' => trim($request->reason),
        ]);

        $label = $request->type === 'CASH_IN' ? 'Kas Masuk' : 'Kas Keluar';
        $formatted = 'Rp ' . number_format($movement->amount, 0, ',', '.');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => "{$label} ({$formatted}) berhasil dicatat!",
                'movement' => $movement,
            ]);
        }

        return redirect()->route('pos.index')->with('success', "{$label} ({$formatted}) berhasil dicatat: {$movement->reason}");
    }

    public function open(Request $request)
    {
        $request->validate([
            'opening_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $existing = CashShift::where('user_id', Auth::id())->where('status', 'OPEN')->first();
        if ($existing) {
            return back()->with('error', 'Anda sudah memiliki shift yang sedang aktif.');
        }

        $shift = CashShift::create([
            'user_id' => Auth::id(),
            'opening_cash' => floatval($request->opening_cash),
            'cash_sales' => 0,
            'non_cash_sales' => 0,
            'expected_cash' => floatval($request->opening_cash),
            'status' => 'OPEN',
            'opened_at' => now(),
            'notes' => $request->notes,
        ]);

        return redirect()->route('pos.index')->with('success', 'Shift kasir berhasil dibuka dengan modal awal Rp ' . number_format($shift->opening_cash, 0, ',', '.'));
    }

    public function close(Request $request, $id = null)
    {
        $query = CashShift::where('status', 'OPEN');
        if ($id) {
            $query->where('id', $id);
            if (!Auth::user()->isAdmin()) {
                $query->where('user_id', Auth::id());
            }
        } else {
            $query->where('user_id', Auth::id());
        }

        $shift = $query->first();
        if (!$shift) {
            return back()->with('error', 'Shift aktif tidak ditemukan.');
        }

        $actualInput = $request->input('actual_cash', $request->input('closing_cash_actual'));
        if ($actualInput === null || !is_numeric($actualInput)) {
            return back()->with('error', 'Jumlah uang fisik di laci wajib diisi berupa angka.');
        }

        $cashSales = Order::where('user_id', $shift->user_id)
            ->where('created_at', '>=', $shift->opened_at)
            ->where('status', 'PAID')
            ->where('payment_method', 'CASH')
            ->sum('grand_total');

        $nonCashSales = Order::where('user_id', $shift->user_id)
            ->where('created_at', '>=', $shift->opened_at)
            ->where('status', 'PAID')
            ->where('payment_method', '!=', 'CASH')
            ->sum('grand_total');

        $cashIn = $shift->totalCashIn();
        $cashOut = $shift->totalCashOut();
        $expectedCash = floatval($shift->opening_cash) + floatval($cashSales) + $cashIn - $cashOut;
        $actualCash = floatval($actualInput);
        $difference = $actualCash - $expectedCash;

        $inputNotes = $request->input('notes', $request->input('closing_notes'));
        $notes = $shift->notes;
        if (!empty($inputNotes)) {
            $notes = $notes ? ($notes . ' | Tutup: ' . $inputNotes) : ('Tutup: ' . $inputNotes);
        }

        $shift->update([
            'cash_sales' => $cashSales,
            'non_cash_sales' => $nonCashSales,
            'expected_cash' => $expectedCash,
            'actual_cash' => $actualCash,
            'difference' => $difference,
            'status' => 'CLOSED',
            'closed_at' => now(),
            'notes' => $notes,
        ]);

        $diffFormatted = number_format(abs($difference), 0, ',', '.');
        $statusMsg = ($difference == 0) ? 'Uang fisik pas!' : (($difference > 0) ? "Selisih LEBIH Rp {$diffFormatted}" : "Selisih KURANG Rp {$diffFormatted}");

        return redirect()->route('pos.index')->with('success', "Shift kasir berhasil ditutup. Total Kas Fisik: Rp " . number_format($actualCash, 0, ',', '.') . " ({$statusMsg})");
    }
}

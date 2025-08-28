<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RecurringPayment;
use App\Models\PaymentSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PaymentScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->hasPermissionTo('maintenance-manage')) {
                abort(403, 'AKSES DITOLAK.');
            }
            return $next($request);
        });
    }

    public function show(RecurringPayment $payment)
    {
        $payment->load('user');
        $schedules = $payment->schedules()
            ->with('paidBy')
            ->orderBy('due_date')
            ->get();

        $totalExpected = $schedules->sum('expected_amount');
        $totalPaid = $schedules->where('status', 'paid')->sum('actual_amount');
        $paidCount = $schedules->where('status', 'paid')->count();
        $pendingCount = $schedules->where('status', 'pending')->count();
        $overdueCount = $schedules->where('status', 'pending')
                                 ->where('due_date', '<', now()->toDateString())
                                 ->count();

        return view('admin.payments.schedules', compact(
            'payment', 
            'schedules', 
            'totalExpected', 
            'totalPaid', 
            'paidCount', 
            'pendingCount', 
            'overdueCount'
        ));
    }

    public function markPaid(Request $request, PaymentSchedule $schedule)
    {
        $request->validate([
            'actual_amount' => 'required|numeric|min:0',
            'paid_date' => 'required|date',
            'payment_method' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
        ]);

        $data = [
            'status' => 'paid',
            'actual_amount' => $request->actual_amount,
            'paid_date' => $request->paid_date,
            'payment_method' => $request->payment_method,
            'notes' => $request->notes,
            'paid_by' => Auth::id(),
            'paid_at' => now(),
        ];

        if ($request->hasFile('attachment')) {
            // Hapus lampiran lama jika ada
            if ($schedule->attachment) {
                Storage::disk('public')->delete($schedule->attachment);
            }
            $path = $request->file('attachment')->store('payment_schedules', 'public');
            $data['attachment'] = $path;
        }

        $schedule->update($data);

        return redirect()->back()->with('success', 'Pembayaran berhasil dicatat.');
    }

    public function markOverdue(PaymentSchedule $schedule)
    {
        $schedule->update(['status' => 'overdue']);
        return redirect()->back()->with('success', 'Status berhasil diubah ke terlambat.');
    }

    public function markCancelled(PaymentSchedule $schedule)
    {
        $schedule->update([
            'status' => 'cancelled',
            'paid_by' => Auth::id(),
            'paid_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Jadwal pembayaran berhasil dibatalkan.');
    }

    public function edit(PaymentSchedule $schedule)
    {
        return view('admin.payments.edit-schedule', compact('schedule'));
    }

    public function update(Request $request, PaymentSchedule $schedule)
    {
        $request->validate([
            'due_date' => 'required|date',
            'expected_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $schedule->update($request->only(['due_date', 'expected_amount', 'notes']));

        return redirect()->route('admin.payment-schedules.show', $schedule->recurring_payment_id)
                        ->with('success', 'Jadwal pembayaran berhasil diperbarui.');
    }
}

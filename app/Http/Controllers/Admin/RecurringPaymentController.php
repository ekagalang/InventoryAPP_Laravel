<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RecurringPayment;
use App\Models\PaymentSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RecurringPaymentController extends Controller
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

    public function index(Request $request)
    {
        $query = RecurringPayment::with('user');

        // Filter berdasarkan kategori
        if ($request->filled('kategori_filter')) {
            $query->where('kategori', $request->kategori_filter);
        }

        // Filter berdasarkan status
        if ($request->filled('status_filter')) {
            $query->where('status', $request->status_filter);
        }

        // Search
        if ($request->filled('search')) {
            $query->where('nama_pembayaran', 'like', '%' . $request->search . '%');
        }

        $payments = $query->latest()->paginate(15);
        return view('admin.payments.index', compact('payments'));
    }

    public function create()
    {
        return view('admin.payments.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_pembayaran' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'kategori' => 'required|in:platform,utilitas,asuransi,sewa,berlangganan,lainnya',
            'tanggal_mulai' => 'required|date',
            'nominal' => 'required|numeric|min:0',
            'status' => 'required|in:aktif,nonaktif,selesai',
            'penerima' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
            'lampiran' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
            'recurrence_interval' => 'required|integer|min:1|max:24',
            'recurrence_unit' => 'required|in:hari,minggu,bulan,tahun',
            'max_occurrences' => 'nullable|integer|min:1|max:120',
            'recurring_end_date' => 'nullable|date|after:tanggal_mulai',
        ]);

        $data = $request->except('lampiran');
        $data['user_id'] = Auth::id();
        $data['is_recurring'] = true;
        $data['recurrence_interval'] = (int) $request->recurrence_interval;
        $data['max_occurrences'] = $request->max_occurrences ? (int) $request->max_occurrences : null;

        if ($request->hasFile('lampiran')) {
            $path = $request->file('lampiran')->store('recurring_payments', 'public');
            $data['lampiran'] = $path;
        }
        
        $payment = RecurringPayment::create($data);

        // Generate jadwal pembayaran otomatis
        if ($payment->is_recurring) {
            $this->generatePaymentSchedules($payment);
        }

        return redirect()->route('admin.payments.index')->with('success', 'Pembayaran rutin berhasil ditambahkan.');
    }

    public function show(RecurringPayment $payment)
    {
        $payment->load('user');
        return view('admin.payments.show', compact('payment'));
    }

    public function edit(RecurringPayment $payment)
    {
        return view('admin.payments.edit', compact('payment'));
    }

    public function update(Request $request, RecurringPayment $payment)
    {
        $request->validate([
            'nama_pembayaran' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'kategori' => 'required|in:platform,utilitas,asuransi,sewa,berlangganan,lainnya',
            'tanggal_mulai' => 'required|date',
            'nominal' => 'required|numeric|min:0',
            'status' => 'required|in:aktif,nonaktif,selesai',
            'penerima' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
            'lampiran' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
            'recurrence_interval' => 'required|integer|min:1|max:24',
            'recurrence_unit' => 'required|in:hari,minggu,bulan,tahun',
            'max_occurrences' => 'nullable|integer|min:1|max:120',
            'recurring_end_date' => 'nullable|date|after:tanggal_mulai',
        ]);

        $data = $request->except('lampiran');
        $data['recurrence_interval'] = (int) $request->recurrence_interval;
        $data['max_occurrences'] = $request->max_occurrences ? (int) $request->max_occurrences : null;

        if ($request->hasFile('lampiran')) {
            if ($payment->lampiran) {
                Storage::disk('public')->delete($payment->lampiran);
            }
            $path = $request->file('lampiran')->store('recurring_payments', 'public');
            $data['lampiran'] = $path;
        }

        $payment->update($data);

        // Hapus jadwal lama yang belum lunas dan buat ulang
        PaymentSchedule::where('recurring_payment_id', $payment->id)
            ->where('status', 'pending')
            ->delete();

        $this->generatePaymentSchedules($payment);

        return redirect()->route('admin.payments.index')->with('success', 'Pembayaran rutin berhasil diperbarui.');
    }

    public function destroy(RecurringPayment $payment)
    {
        if ($payment->lampiran) {
            Storage::disk('public')->delete($payment->lampiran);
        }

        $payment->delete();
        return redirect()->route('admin.payments.index')->with('success', 'Pembayaran rutin berhasil dihapus.');
    }

    private function generatePaymentSchedules(RecurringPayment $payment)
    {
        if (!$payment->is_recurring) {
            return;
        }

        $startDate = $payment->tanggal_mulai->copy();
        $currentDate = $startDate->copy();
        $count = 0;
        $maxOccurrences = (int) ($payment->max_occurrences ?: 24);
        $endDate = $payment->recurring_end_date;
        $interval = (int) $payment->recurrence_interval;
        
        while ($count < $maxOccurrences) {
            // Generate jadwal berikutnya
            if ($payment->recurrence_unit === 'hari') {
                $currentDate = $currentDate->addDays($interval);
            } elseif ($payment->recurrence_unit === 'minggu') {
                $currentDate = $currentDate->addWeeks($interval);
            } elseif ($payment->recurrence_unit === 'bulan') {
                $currentDate = $currentDate->addMonths($interval);
            } elseif ($payment->recurrence_unit === 'tahun') {
                $currentDate = $currentDate->addYears($interval);
            } else {
                break;
            }
            
            // Cek apakah sudah melewati batas tanggal
            if ($endDate && $currentDate->gt($endDate)) {
                break;
            }
            
            // Buat jadwal
            PaymentSchedule::create([
                'recurring_payment_id' => $payment->id,
                'due_date' => $currentDate->copy(),
                'expected_amount' => (float) $payment->nominal,
                'status' => 'pending',
            ]);
            
            $count++;
        }
    }
}

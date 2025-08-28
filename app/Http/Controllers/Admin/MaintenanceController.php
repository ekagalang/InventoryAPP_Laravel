<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use App\Models\MaintenanceSchedule;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MaintenanceController extends Controller
{
    // Permission check bisa ditambahkan di sini atau di constructor
    public function __construct()
    {
        // Misalnya kita buat permission baru 'maintenance-manage'
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->hasPermissionTo('maintenance-manage')) {
                abort(403, 'AKSES DITOLAK.');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $query = Maintenance::with('barang', 'pencatat');

        // Fitur Filter
        if ($request->filled('search')) {
            $query->where('nama_perbaikan', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('status_filter')) {
            $query->where('status', $request->status_filter);
        }

        $maintenances = $query->latest()->paginate(15);
        return view('admin.maintenances.index', compact('maintenances'));
    }

    public function create()
    {
        $barangs = Barang::where('status', 'aktif')->orderBy('nama_barang')->get();
        return view('admin.maintenances.create', compact('barangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_perbaikan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'barang_id' => 'nullable|exists:barangs,id',
            'tanggal_maintenance' => 'required|date',
            'biaya' => 'nullable|numeric|min:0',
            'status' => 'required|in:Dijadwalkan,Selesai,Dibatalkan',
            'lampiran' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
            'recurrence_interval' => 'nullable|integer|min:1|max:24',
            'recurrence_unit' => 'nullable|in:bulan,tahun',
            'max_occurrences' => 'nullable|integer|min:1|max:60',
            'recurring_end_date' => 'nullable|date|after:tanggal_maintenance',
        ]);

        $data = $request->except('lampiran'); // Ambil semua data kecuali file
        $data['user_id'] = Auth::id();
        $data['is_recurring'] = $request->has('is_recurring');

        if (!$data['is_recurring']) {
            $data['recurrence_interval'] = null;
            $data['recurrence_unit'] = null;
            $data['max_occurrences'] = null;
            $data['recurring_end_date'] = null;
        } else {
            // Cast ke integer untuk menghindari error Carbon
            $data['recurrence_interval'] = (int) $request->recurrence_interval;
            $data['max_occurrences'] = $request->max_occurrences ? (int) $request->max_occurrences : null;
        }

        if ($request->hasFile('lampiran')) {
            $path = $request->file('lampiran')->store('lampiran_maintenance', 'public');
            $data['lampiran'] = $path;
        }
        
        $maintenance = Maintenance::create($data);

        // Jika maintenance berulang, buat jadwal otomatis
        if ($maintenance->is_recurring) {
            $this->generateRecurringSchedules($maintenance);
        }

        return redirect()->route('admin.maintenances.index')->with('success', 'Jadwal maintenance berhasil ditambahkan.');
    }

    public function edit(Maintenance $maintenance)
    {
        $barangs = Barang::where('status', 'aktif')->orderBy('nama_barang')->get();
        return view('admin.maintenances.edit', compact('maintenance', 'barangs'));
    }

    public function update(Request $request, Maintenance $maintenance)
    {
        $request->validate([
            'nama_perbaikan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'barang_id' => 'nullable|exists:barangs,id',
            'tanggal_maintenance' => 'required|date',
            'biaya' => 'nullable|numeric|min:0',
            'status' => 'required|in:Dijadwalkan,Selesai,Dibatalkan',
            'lampiran' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
            'recurrence_interval' => 'nullable|integer|min:1|max:24',
            'recurrence_unit' => 'nullable|in:bulan,tahun',
            'max_occurrences' => 'nullable|integer|min:1|max:60',
            'recurring_end_date' => 'nullable|date|after:tanggal_maintenance',
        ]);

        $data = $request->except('lampiran');
        $data['is_recurring'] = $request->has('is_recurring');

        if (!$data['is_recurring']) {
            $data['recurrence_interval'] = null;
            $data['recurrence_unit'] = null;
            $data['max_occurrences'] = null;
            $data['recurring_end_date'] = null;
        } else {
            // Cast ke integer untuk menghindari error Carbon
            $data['recurrence_interval'] = (int) $request->recurrence_interval;
            $data['max_occurrences'] = $request->max_occurrences ? (int) $request->max_occurrences : null;
        }

        if ($request->hasFile('lampiran')) {
            // Hapus lampiran lama jika ada
            if ($maintenance->lampiran) {
                Storage::disk('public')->delete($maintenance->lampiran);
            }
            // Simpan lampiran baru
            $path = $request->file('lampiran')->store('lampiran_maintenance', 'public');
            $data['lampiran'] = $path;
        }

        $maintenance->update($data);

        // Hapus jadwal lama yang belum selesai dan buat ulang jika berulang
        MaintenanceSchedule::where('maintenance_id', $maintenance->id)
            ->where('status', 'pending')
            ->delete();

        if ($maintenance->is_recurring) {
            $this->generateRecurringSchedules($maintenance);
        }

        return redirect()->route('admin.maintenances.index')->with('success', 'Jadwal maintenance berhasil diperbarui.');
    }

    public function destroy(Maintenance $maintenance)
    {
        if ($maintenance->lampiran) {
            Storage::disk('public')->delete($maintenance->lampiran);
        }

        $maintenance->delete();
        return redirect()->route('admin.maintenances.index')->with('success', 'Jadwal maintenance berhasil dihapus.');
    }

    public function show(Maintenance $maintenance)
    {
        // Permission check, sama dengan index
        if (!Auth::user()->hasPermissionTo('maintenance-manage')) {
            abort(403, 'AKSES DITOLAK.');
        }

        // Eager load relasi untuk memastikan data tersedia di view
        $maintenance->load('barang', 'pencatat');

        return view('admin.maintenances.show', compact('maintenance'));
    }

    private function generateRecurringSchedules(Maintenance $maintenance)
    {
        if (!$maintenance->is_recurring) {
            return;
        }

        $startDate = $maintenance->tanggal_maintenance->copy();
        $currentDate = $startDate->copy();
        $count = 0;
        $maxOccurrences = (int) ($maintenance->max_occurrences ?: 12);
        $endDate = $maintenance->recurring_end_date;
        $interval = (int) $maintenance->recurrence_interval;
        
        // Include the first/initial date as the first scheduled maintenance
        MaintenanceSchedule::create([
            'maintenance_id' => $maintenance->id,
            'scheduled_date' => $currentDate->copy(),
            'estimated_cost' => (float) ($maintenance->biaya ?? 0),
            'status' => 'pending',
        ]);
        $count++;
        
        while ($count < $maxOccurrences) {
            // Generate jadwal berikutnya
            if ($maintenance->recurrence_unit === 'bulan') {
                $currentDate = $currentDate->addMonths($interval);
            } elseif ($maintenance->recurrence_unit === 'tahun') {
                $currentDate = $currentDate->addYears($interval);
            } else {
                break; // Unit tidak valid
            }
            
            // Cek apakah sudah melewati batas tanggal
            if ($endDate && $currentDate->gt($endDate)) {
                break;
            }
            
            // Buat jadwal
            MaintenanceSchedule::create([
                'maintenance_id' => $maintenance->id,
                'scheduled_date' => $currentDate->copy(),
                'estimated_cost' => (float) ($maintenance->biaya ?? 0),
                'status' => 'pending',
            ]);
            
            $count++;
        }
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use App\Models\MaintenanceSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MaintenanceScheduleController extends Controller
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

    public function show(Maintenance $maintenance)
    {
        $maintenance->load('barang', 'pencatat');
        $schedules = $maintenance->schedules()
            ->with('completedBy')
            ->orderBy('scheduled_date')
            ->get();

        $totalEstimated = $schedules->sum('estimated_cost');
        $totalActual = $schedules->where('status', 'completed')->sum('actual_cost');
        $completedCount = $schedules->where('status', 'completed')->count();
        $pendingCount = $schedules->where('status', 'pending')->count();
        $overdueCount = $schedules->where('status', 'pending')
                                 ->where('scheduled_date', '<', now()->toDateString())
                                 ->count();

        return view('admin.maintenances.schedules', compact(
            'maintenance', 
            'schedules', 
            'totalEstimated', 
            'totalActual', 
            'completedCount', 
            'pendingCount', 
            'overdueCount'
        ));
    }

    public function markCompleted(Request $request, MaintenanceSchedule $schedule)
    {
        $request->validate([
            'actual_cost' => 'required|numeric|min:0',
            'completed_date' => 'required|date',
            'work_method' => 'nullable|string',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
        ]);

        $data = [
            'status' => 'completed',
            'actual_cost' => $request->actual_cost,
            'completed_date' => $request->completed_date,
            'work_method' => $request->work_method,
            'notes' => $request->notes,
            'completed_by' => Auth::id(),
            'completed_at' => now(),
        ];

        if ($request->hasFile('attachment')) {
            // Hapus lampiran lama jika ada
            if ($schedule->attachment) {
                Storage::disk('public')->delete($schedule->attachment);
            }
            $path = $request->file('attachment')->store('maintenance_schedules', 'public');
            $data['attachment'] = $path;
        }

        $schedule->update($data);

        return redirect()->back()->with('success', 'Jadwal maintenance berhasil ditandai selesai.');
    }

    public function markCancelled(MaintenanceSchedule $schedule)
    {
        $schedule->update([
            'status' => 'cancelled',
            'completed_by' => Auth::id(),
            'completed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Jadwal maintenance berhasil dibatalkan.');
    }

    public function edit(MaintenanceSchedule $schedule)
    {
        return view('admin.maintenances.edit-schedule', compact('schedule'));
    }

    public function update(Request $request, MaintenanceSchedule $schedule)
    {
        $request->validate([
            'scheduled_date' => 'required|date',
            'estimated_cost' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $schedule->update($request->only(['scheduled_date', 'estimated_cost', 'notes']));

        return redirect()->route('admin.maintenances.schedules', $schedule->maintenance_id)
                        ->with('success', 'Jadwal maintenance berhasil diperbarui.');
    }
}

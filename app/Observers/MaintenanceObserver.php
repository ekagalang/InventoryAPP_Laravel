<?php
namespace App\Observers;

use App\Models\Maintenance;
use Carbon\Carbon;

class MaintenanceObserver
{
    public function updated(Maintenance $maintenance): void
    {
        // Cek apakah kolom 'status' baru saja diubah menjadi 'Selesai' DAN jadwal ini berulang
        if ($maintenance->wasChanged('status') && $maintenance->status === 'Selesai' && $maintenance->is_recurring) {

            // Hitung tanggal maintenance berikutnya
            $currentDate = Carbon::parse($maintenance->tanggal_maintenance);

            $nextDate = match ($maintenance->recurrence_unit) {
                'hari' => $currentDate->addDays($maintenance->recurrence_interval),
                'minggu' => $currentDate->addWeeks($maintenance->recurrence_interval),
                'bulan' => $currentDate->addMonths($maintenance->recurrence_interval),
                'tahun' => $currentDate->addYears($maintenance->recurrence_interval),
                default => null,
            };

            // Jika tanggal berikutnya valid, buat jadwal baru
            if ($nextDate) {
                // Buat duplikat dari maintenance yang baru selesai
                $newMaintenance = $maintenance->replicate()->fill([
                    'tanggal_maintenance' => $nextDate,
                    'status' => 'Dijadwalkan', // Set status kembali ke Dijadwalkan
                    'biaya' => 0, // Reset biaya
                    'lampiran' => null, // Reset lampiran
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $newMaintenance->save();
            }
        }
    }
}
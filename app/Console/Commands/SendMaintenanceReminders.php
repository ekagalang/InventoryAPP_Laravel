<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Maintenance;
use App\Models\User;
use App\Notifications\MaintenanceReminderNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class SendMaintenanceReminders extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'maintenance:send-reminders';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Periksa jadwal maintenance yang akan datang dan kirim notifikasi pengingat';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai pengecekan jadwal maintenance...');
        Log::info('Scheduler Maintenance Reminder: Mulai berjalan.');

        // Tentukan berapa hari sebelum maintenance notifikasi akan dikirim
        $reminderDays = [7, 3, 1]; // Kirim H-7, H-3, dan H-1

        $usersToNotify = User::role(['Admin', 'StafGudang'])->get();
        if ($usersToNotify->isEmpty()) {
            $this->warn('Tidak ada user (Admin/StafGudang) yang ditemukan untuk menerima notifikasi. Proses dihentikan.');
            Log::warning('Scheduler Maintenance Reminder: Tidak ada user target.');
            return;
        }

        $sentNotificationsCount = 0;

        foreach ($reminderDays as $days) {
            // Cari jadwal maintenance yang statusnya 'Dijadwalkan' dan tanggalnya adalah 'n' hari dari sekarang
            $targetDate = now()->addDays($days)->toDateString();

            $maintenances = Maintenance::where('status', 'Dijadwalkan')
                                        ->whereDate('tanggal_maintenance', $targetDate)
                                        ->get();

            if ($maintenances->isNotEmpty()) {
                $this->info("Menemukan " . $maintenances->count() . " jadwal untuk H-{$days} (Tanggal: {$targetDate}). Mengirim notifikasi...");
                Log::info("Scheduler Maintenance Reminder: Menemukan " . $maintenances->count() . " jadwal untuk H-{$days}.");

                foreach ($maintenances as $maintenance) {
                    Notification::send($usersToNotify, new MaintenanceReminderNotification($maintenance));
                    $sentNotificationsCount++;
                }
            } else {
                 $this->info("Tidak ada jadwal maintenance untuk H-{$days}.");
            }
        }

        $this->info("Proses selesai. Total notifikasi terkirim: {$sentNotificationsCount}.");
        Log::info("Scheduler Maintenance Reminder: Selesai. Total notifikasi terkirim: {$sentNotificationsCount}.");

        return Command::SUCCESS;
    }
}
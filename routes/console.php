<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 2. TAMBAHKAN PENJADWALAN ANDA DI SINI
Schedule::command('maintenance:send-reminders')->dailyAt('08:00');

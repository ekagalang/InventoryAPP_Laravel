<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
// Untuk Laravel 9+ biasanya menggunakan ValidatesRequests.
// Untuk Laravel 8 ke bawah, mungkin menggunakan DispatchesJobs.
// Jika Anda menggunakan Laravel 10+, ValidatesRequests sudah ada.
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    // Jika ValidatesRequests tidak ada atau error, coba:
    // use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    // atau hanya use AuthorizesRequests; jika yang lain tidak diperlukan langsung di base controller Anda.
    // Namun, untuk fungsionalitas middleware, yang penting adalah extends BaseController.
}
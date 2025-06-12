<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index()
    {
        // Kita akan buat permission ini nanti
        if (!Auth::user()->hasPermissionTo('view-audit-trail')) {
            abort(403, 'AKSES DITOLAK.');
        }

        $activities = Activity::with('causer', 'subject') // Eager load relasi untuk efisiensi
                                ->latest() // Urutkan dari yang terbaru
                                ->paginate(25);

        return view('admin.activity_logs.index', compact('activities'));
    }
}
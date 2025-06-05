<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Pastikan Auth di-import

class NotificationController extends Controller
{
    public function __construct()
    {
        // Anda bisa tambahkan middleware 'auth' di sini jika route-nya belum dilindungi
        // $this->middleware('auth');
    }

    /**
     * Menampilkan semua notifikasi pengguna.
     */
public function index()
{
    $user = Auth::user();
    $notifications = $user->notifications()->paginate(15); // Ambil semua, paginasi

    // Opsional: Saat halaman ini dibuka, tandai semua yang belum dibaca sebagai dibaca
    // $user->unreadNotifications->markAsRead(); 
    // Jika Anda ingin ini terjadi otomatis saat halaman dibuka, uncomment baris di atas.
    // Atau, biarkan pengguna menandainya manual melalui tombol.

    return view('notifikasi.index', compact('notifications'));
}

    /**
     * Menandai notifikasi sebagai sudah dibaca dan redirect ke URL tujuan.
     */
    public function markAsReadAndRedirect(Request $request, $id)
    {
        $user = Auth::user();
        // Cari notifikasi berdasarkan ID HANYA milik user yang login
        $notification = $user->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead(); // Tandai sudah dibaca
            
            // Ambil URL tujuan dari data notifikasi.
            // Jika tidak ada 'url' di data, redirect ke dashboard sebagai fallback.
            $redirectUrl = $notification->data['url'] ?? route('dashboard'); 
            return redirect($redirectUrl);
        }

        // Jika notifikasi tidak ditemukan (mungkin milik user lain atau ID salah)
        return redirect()->route('dashboard')->with('error', 'Notifikasi tidak ditemukan.');
    }
    
    /**
     * Menandai semua notifikasi yang belum dibaca sebagai sudah dibaca.
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead(); // Hanya menandai yang belum dibaca
        return back()->with('success', 'Semua notifikasi telah ditandai sudah dibaca.');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_perbaikan',
        'deskripsi',
        'barang_id',
        'tanggal_maintenance',
        'biaya',
        'status',
        'lampiran',
        'is_recurring',
        'recurrence_interval',
        'recurrence_unit',
        'user_id',
    ];

    // Relasi ke model User (siapa yang mencatat)
    public function pencatat()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke model Barang (barang yang di-maintenance)
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }
}
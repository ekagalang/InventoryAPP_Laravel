<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo

class ItemRequest extends Model
{
    use HasFactory;

    protected $table = 'item_requests'; // Eksplisit mendefinisikan nama tabel jika berbeda dari konvensi jamak

    protected $fillable = [
        'user_id',
        'barang_id',
        'kuantitas_diminta',
        'kuantitas_disetujui',
        'keperluan',
        'tanggal_dibutuhkan',
        'status',
        'approved_by',
        'approved_at',
        'processed_by',
        'processed_at',
        'catatan_approval',
        'catatan_pemroses',
    ];

    protected $casts = [
        'tanggal_dibutuhkan' => 'date', // Casting ke objek Carbon Date
        'approved_at' => 'datetime',   // Casting ke objek Carbon DateTime
        'processed_at' => 'datetime',  // Casting ke objek Carbon DateTime
        'kuantitas_diminta' => 'integer',
        'kuantitas_disetujui' => 'integer',
    ];

    /**
     * Pengajuan ini dibuat oleh satu User (Pemohon).
     */
    public function pemohon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Barang yang diajukan dalam pengajuan ini.
     */
    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    /**
     * Pengajuan ini disetujui/ditolak oleh satu User (Approver).
     * Relasi ini bisa null jika belum ada aksi approval.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Pengajuan ini diproses (barang dikeluarkan) oleh satu User (Pemroses).
     * Relasi ini bisa null jika belum diproses.
     */
    public function pemroses(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
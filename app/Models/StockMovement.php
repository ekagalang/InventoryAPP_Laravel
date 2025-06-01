<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'barang_id',
        'user_id',
        'tipe_pergerakan',
        'kuantitas',
        'stok_sebelumnya',
        'stok_setelahnya',
        'tanggal_pergerakan',
        'catatan',
    ];

    // Casting tipe data agar lebih mudah diolah
    protected $casts = [
        'tanggal_pergerakan' => 'datetime',
        'kuantitas' => 'integer',
        'stok_sebelumnya' => 'integer',
        'stok_setelahnya' => 'integer',
    ];

    /**
     * Pergerakan stok ini dimiliki oleh satu Barang.
     */
    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    /**
     * Pergerakan stok ini dicatat oleh satu User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
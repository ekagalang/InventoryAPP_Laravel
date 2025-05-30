<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_barang',
        'kode_barang',
        'deskripsi',
        'kategori_id',
        // 'unit_id',
        // 'lokasi_id',
        'stok',
        'harga_beli',
        'gambar',
        'status',
    ];

    public function kategori() // Nama method tunggal, karena satu barang hanya milik SATU kategori
    {
        // Argumen kedua (foreign_key) dan ketiga (owner_key) biasanya bisa ditebak Laravel
        // jika Anda mengikuti konvensi penamaan (kategori_id).
        return $this->belongsTo(Kategori::class);
    }

    // Relasi akan ditambahkan di sini nanti
}
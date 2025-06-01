<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lokasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_lokasi',
        'kode_lokasi',
        'deskripsi_lokasi',
    ];

    /**
     * Lokasi memiliki banyak Barang.
     */
    public function barangs()
    {
        return $this->hasMany(Barang::class);
    }
}
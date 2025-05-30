<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_kategori',
        'deskripsi_kategori',
    ];

    /**
     * Kategori memiliki banyak Barang.
     */
    public function barangs() // Nama method jamak, karena satu kategori bisa punya BANYAK barang
    {
        return $this->hasMany(Barang::class);
    }
}
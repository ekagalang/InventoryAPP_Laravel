<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_unit',
        'singkatan_unit',
        'deskripsi_unit',
    ];

    /**
     * Unit memiliki banyak Barang.
     */
    public function barangs()
    {
        return $this->hasMany(Barang::class);
    }
}
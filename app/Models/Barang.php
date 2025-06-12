<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Barang extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'nama_barang',
        'tipe_item',
        'kode_barang',
        'deskripsi',
        'kategori_id',
        'unit_id',
        'lokasi_id',
        'stok',
        'stok_minimum',
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

    public function unit() // <--- PERHATIKAN INI
    {
        return $this->belongsTo(Unit::class);
        // Argumen kedua (foreign_key 'unit_id') dan ketiga (owner_key 'id')
        // biasanya bisa ditebak Laravel jika Anda mengikuti konvensi.
    }

    public function lokasi()
    {
        return $this->belongsTo(Lokasi::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function itemRequests(): HasMany
    {
        return $this->hasMany(ItemRequest::class, 'barang_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nama_barang', 'tipe_item', 'kategori_id', 'stok', 'stok_minimum', 'harga_beli', 'status']) // Catat hanya perubahan pada kolom-kolom ini
            ->logOnlyDirty() // Hanya catat jika ada perubahan (saat update)
            ->setDescriptionForEvent(fn(string $eventName) => "Data barang '{$this->nama_barang}' telah di-{$eventName}")
            ->useLogName('Barang'); // Nama log untuk memfilter
    }

    // Relasi akan ditambahkan di sini nanti
}
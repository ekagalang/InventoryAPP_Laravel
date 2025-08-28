<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecurringPayment extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'nama_pembayaran',
        'deskripsi',
        'kategori',
        'tanggal_mulai',
        'nominal',
        'status',
        'penerima',
        'keterangan',
        'lampiran',
        'is_recurring',
        'recurrence_interval',
        'recurrence_unit',
        'max_occurrences',
        'recurring_end_date',
        'user_id',
    ];
    
    protected $casts = [
        'tanggal_mulai' => 'date',
        'recurring_end_date' => 'date',
        'is_recurring' => 'boolean',
        'nominal' => 'decimal:2',
        'recurrence_interval' => 'integer',
        'max_occurrences' => 'integer',
    ];
    
    // Relasi ke User (pembuat)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // Relasi ke jadwal pembayaran
    public function schedules()
    {
        return $this->hasMany(PaymentSchedule::class);
    }
    
    // Scope untuk status aktif
    public function scopeActive($query)
    {
        return $query->where('status', 'aktif');
    }
    
    // Scope untuk kategori tertentu
    public function scopeByCategory($query, $category)
    {
        return $query->where('kategori', $category);
    }
    
    // Get kategori dalam format readable
    public function getKategoriLabelAttribute()
    {
        $labels = [
            'platform' => 'Platform Digital',
            'utilitas' => 'Utilitas (IPL)',
            'asuransi' => 'Asuransi',
            'sewa' => 'Sewa',
            'berlangganan' => 'Berlangganan',
            'lainnya' => 'Lainnya'
        ];
        
        return $labels[$this->kategori] ?? $this->kategori;
    }
}

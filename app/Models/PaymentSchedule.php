<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSchedule extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'recurring_payment_id',
        'due_date',
        'expected_amount',
        'actual_amount',
        'status',
        'paid_date',
        'payment_method',
        'notes',
        'attachment',
        'paid_by',
        'paid_at',
    ];
    
    protected $casts = [
        'due_date' => 'date',
        'paid_date' => 'date',
        'expected_amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];
    
    // Relasi ke RecurringPayment
    public function recurringPayment()
    {
        return $this->belongsTo(RecurringPayment::class);
    }
    
    // Relasi ke User yang membayar
    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }
    
    // Scope untuk status pending
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    // Scope untuk pembayaran yang sudah lunas
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
    
    // Scope untuk yang terlambat
    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
                    ->where('due_date', '<', now()->toDateString());
    }
    
    // Check apakah pembayaran terlambat
    public function getIsOverdueAttribute()
    {
        return $this->status === 'pending' && $this->due_date->isPast();
    }
    
    // Get selisih pembayaran
    public function getDifferenceAmountAttribute()
    {
        if (!$this->actual_amount) {
            return 0;
        }
        
        return $this->actual_amount - $this->expected_amount;
    }
    
    // Get persentase selisih
    public function getDifferencePercentageAttribute()
    {
        if (!$this->actual_amount || $this->expected_amount == 0) {
            return 0;
        }
        
        return (($this->actual_amount - $this->expected_amount) / $this->expected_amount) * 100;
    }
}

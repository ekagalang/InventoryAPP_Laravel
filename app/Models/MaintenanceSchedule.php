<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceSchedule extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'maintenance_id',
        'scheduled_date',
        'estimated_cost',
        'actual_cost',
        'status',
        'notes',
        'attachment',
        'completed_by',
        'completed_at',
    ];
    
    protected $casts = [
        'scheduled_date' => 'date',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'completed_at' => 'datetime',
    ];
    
    public function maintenance()
    {
        return $this->belongsTo(Maintenance::class);
    }
    
    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
    
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
    
    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
                    ->where('scheduled_date', '<', now()->toDateString());
    }
}

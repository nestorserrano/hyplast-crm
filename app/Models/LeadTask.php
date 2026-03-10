<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'assigned_to',
        'title',
        'description',
        'due_date',
        'is_completed',
        'completed_at',
        'start_date',
        'end_date',
        'status',
        'is_started',
        'created_by',
        'notification_sent',
        'position'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_started' => 'boolean',
        'notification_sent' => 'boolean'
    ];

    protected $appends = ['status_label', 'is_overdue', 'days_remaining'];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Accessors
    public function getStatusLabelAttribute()
    {
        return match($this->status ?? 'nuevo') {
            'nuevo' => 'Nuevo',
            'en_proceso' => 'En Proceso',
            'finalizado' => 'Finalizado',
            default => 'Nuevo'
        };
    }

    public function getIsOverdueAttribute()
    {
        if (!$this->end_date) return false;
        return ($this->status ?? 'nuevo') !== 'finalizado' && $this->end_date->isPast();
    }

    public function getDaysRemainingAttribute()
    {
        if (!$this->end_date || ($this->status ?? 'nuevo') === 'finalizado') {
            return 0;
        }
        return now()->diffInDays($this->end_date, false);
    }

    // Scopes
    public function scopeNuevo($query)
    {
        return $query->where('status', 'nuevo');
    }

    public function scopeEnProceso($query)
    {
        return $query->where('status', 'en_proceso');
    }

    public function scopeFinalizado($query)
    {
        return $query->where('status', 'finalizado');
    }

    public function scopeStartsToday($query)
    {
        return $query->whereDate('start_date', today())
                     ->where('status', 'nuevo')
                     ->where('notification_sent', false);
    }

    // Métodos helper
    public function moveToEnProceso()
    {
        $this->update([
            'status' => 'en_proceso',
            'is_started' => true
        ]);
    }

    public function moveToFinalizado()
    {
        $this->update(['status' => 'finalizado']);
    }

    public function markAsStarted()
    {
        $this->update(['is_started' => true]);
    }
}

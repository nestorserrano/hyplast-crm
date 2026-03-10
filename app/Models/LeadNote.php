<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lead_id',
        'user_id',
        'content',
        'nota_sin_formato',
        'type',
        'is_pinned',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con Lead
     */
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Relación con User (autor de la nota)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para notas destacadas
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    /**
     * Scope para ordenar por fecha descendente
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Obtener icono según tipo de nota
     */
    public function getIconAttribute()
    {
        if (!$this->type) {
            return 'fas fa-sticky-note';
        }

        return match($this->type) {
            'email' => 'fas fa-envelope',
            'call' => 'fas fa-phone',
            'meeting' => 'fas fa-calendar-alt',
            'whatsapp' => 'fab fa-whatsapp',
            default => 'fas fa-sticky-note',
        };
    }

    /**
     * Obtener color según tipo de nota
     */
    public function getColorAttribute()
    {
        if (!$this->type) {
            return 'secondary';
        }

        return match($this->type) {
            'email' => 'danger',
            'call' => 'info',
            'meeting' => 'warning',
            'whatsapp' => 'success',
            default => 'secondary',
        };
    }
}

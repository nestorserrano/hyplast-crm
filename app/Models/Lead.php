<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'conjunto_id',
        'name',
        'phone',
        'email',
        'website',
        'company',
        'country',
        'country_id',
        'state_id',
        'city_id',
        'notes',
        'lead_status_id',
        'assigned_to',
        'vendedor_id',
        'created_by',
        'source',
        'lead_source_id',
        'preferred_channel',
        'expected_close_date',
        'priority',
        'last_contact_at',
    ];

    protected $casts = [
        'expected_close_date' => 'date',
        'last_contact_at' => 'datetime',
        'assigned_to' => 'integer',
    ];

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-asignar conjunto_id al crear
        static::creating(function ($lead) {
            if (!$lead->conjunto_id) {
                $lead->conjunto_id = \App\Helpers\SchemaHelper::getSchema();
            }
        });

        // Actualizar last_contact_at al editar el lead
        static::updating(function ($lead) {
            // Solo actualizar si se modificaron campos relevantes (no last_contact_at mismo)
            if ($lead->isDirty() && !$lead->isDirty('last_contact_at')) {
                $lead->last_contact_at = now();
            }
        });

        // Filtrar por conjunto del usuario
        static::addGlobalScope('conjunto', function ($builder) {
            $conjunto = \App\Helpers\SchemaHelper::getSchema();
            if ($conjunto) {
                $builder->where('leads.conjunto_id', $conjunto);
            }
        });
    }

    public function status()
    {
        return $this->belongsTo(LeadStatus::class, 'lead_status_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id', 'vendedor_id');
    }

    public function vendedorSoftland()
    {
        $schema = $this->conjunto_id ?? \App\Helpers\SchemaHelper::getSchema();
        return $this->belongsTo(Vendedor::class, 'vendedor_id', 'VENDEDOR')
            ->from("{$schema}.VENDEDOR");
    }

    public function countryInfo()
    {
        $schema = $this->conjunto_id ?? \App\Helpers\SchemaHelper::getSchema();
        return $this->belongsTo(Country::class, 'country', 'PAIS')
            ->from("{$schema}.PAIS");
    }

    /**
     * Relación con CountryProduction (tablas de producción)
     */
    public function countryProduction()
    {
        return $this->belongsTo(CountryProduction::class, 'country_id');
    }

    /**
     * Relación con State (estado/provincia)
     */
    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    /**
     * Relación con City (ciudad)
     */
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function leadSource()
    {
        return $this->belongsTo(LeadSource::class, 'lead_source_id');
    }

    public function conversations()
    {
        return $this->hasMany(CrmConversation::class);
    }

    public function activities()
    {
        return $this->hasMany(LeadActivity::class);
    }

    public function tasks()
    {
        return $this->hasMany(LeadTask::class);
    }

    public function notes()
    {
        return $this->hasMany(LeadNote::class);
    }

    /**
     * Relación con correos del lead
     */
    public function emails()
    {
        return $this->hasMany(LeadEmail::class)->latest();
    }

    /**
     * Obtener todas las actualizaciones del lead en orden cronológico
     * Combina activities, notes, y tasks
     */
    public function getTimelineAttribute()
    {
        $timeline = collect();

        // Agregar actividades
        if ($this->relationLoaded('activities') && $this->activities !== null) {
            foreach ($this->activities as $activity) {
                $timeline->push([
                    'type' => 'activity',
                    'icon' => 'fas fa-history',
                    'color' => 'info',
                    'title' => $activity->type ?? 'Actividad',
                    'description' => $activity->description ?? '',
                    'user' => $activity->user ?? null,
                    'date' => $activity->activity_date ?? $activity->created_at,
                    'created_at' => $activity->created_at,
                ]);
            }
        }

        // Agregar notas
        if ($this->relationLoaded('notes') && $this->notes !== null) {
            foreach ($this->notes as $note) {
                // Saltar notas sin tipo
                if (!isset($note->type) || empty($note->type)) {
                    continue;
                }

                // Obtener icon y color de forma segura
                try {
                    $icon = $note->icon ?? 'fas fa-sticky-note';
                    $color = $note->color ?? 'secondary';
                } catch (\Exception $e) {
                    $icon = 'fas fa-sticky-note';
                    $color = 'secondary';
                }

                $timeline->push([
                    'type' => 'note',
                    'icon' => $icon,
                    'color' => $color,
                    'title' => ucfirst($note->type),
                    'description' => $note->nota_sin_formato ?? strip_tags($note->content ?? ''),
                    'user' => $note->user ?? null,
                    'date' => $note->created_at,
                    'created_at' => $note->created_at,
                    'is_pinned' => $note->is_pinned ?? false,
                ]);
            }
        }

        // Agregar tareas
        if ($this->relationLoaded('tasks') && $this->tasks !== null) {
            foreach ($this->tasks as $task) {
                $timeline->push([
                    'type' => 'task',
                    'icon' => $task->is_completed ? 'fas fa-check-circle' : 'fas fa-tasks',
                    'color' => $task->is_completed ? 'success' : 'warning',
                    'title' => 'Tarea: ' . ($task->title ?? 'Sin título'),
                    'description' => $task->description ?? '',
                    'user' => $task->assignedTo ?? null,
                    'date' => $task->due_date ?? $task->created_at,
                    'created_at' => $task->created_at,
                ]);
            }
        }

        // Ordenar por fecha descendente
        return $timeline->sortByDesc('created_at');
    }

    public function getPriorityTextAttribute()
    {
        return match($this->priority) {
            1 => 'Alta',
            2 => 'Media',
            3 => 'Baja',
            default => 'Media',
        };
    }

    public function getPriorityBadgeAttribute()
    {
        return match($this->priority) {
            1 => 'badge-danger',
            2 => 'badge-warning',
            3 => 'badge-info',
            default => 'badge-secondary',
        };
    }
}

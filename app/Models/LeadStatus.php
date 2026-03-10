<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class LeadStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'conjunto_id',
        'name',
        'color',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-asignar conjunto_id al crear
        static::creating(function ($status) {
            if (!$status->conjunto_id) {
                $status->conjunto_id = \App\Helpers\SchemaHelper::getSchema();
            }
        });

        // Filtrar por conjunto del usuario
        static::addGlobalScope('conjunto', function ($builder) {
            $conjunto = \App\Helpers\SchemaHelper::getSchema();
            if ($conjunto) {
                $builder->where('lead_statuses.conjunto_id', $conjunto);
            }
        });
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }
}

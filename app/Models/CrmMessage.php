<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmMessage extends Model
{
    use HasFactory;

    protected $table = 'crm_messages';

    protected $fillable = [
        'conversation_id',
        'user_id',
        'content',
        'type',
        'media_url',
        'is_from_lead',
        'is_read',
        'read_at',
        'direction',
        'status',
        'whatsapp_message_id',
    ];

    protected $casts = [
        'is_from_lead' => 'boolean',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(CrmConversation::class, 'conversation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

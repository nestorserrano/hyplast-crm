<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmConversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'crm_conversations';

    protected $fillable = [
        'lead_id',
        'vendedor_id',
        'channel',
        'status',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function messages()
    {
        return $this->hasMany(CrmMessage::class, 'conversation_id');
    }

    public function lastMessage()
    {
        return $this->hasOne(CrmMessage::class, 'conversation_id')->latestOfMany();
    }

    public function unreadCount()
    {
        return $this->messages()->where('is_from_lead', true)->where('is_read', false)->count();
    }
}

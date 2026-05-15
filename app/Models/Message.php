<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
  
    protected $fillable = [
        'conversation_id', 
        'sender_id', 
        'body', 
        'type', 
        'file_url', 
        'file_name', 
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
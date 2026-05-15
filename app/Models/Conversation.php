<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = [
        'parent_id', 'physio_id', 'screening_id', 'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function physio()
    {
        return $this->belongsTo(User::class, 'physio_id');
    }

    public function screening()
    {
        return $this->belongsTo(Screening::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }
}
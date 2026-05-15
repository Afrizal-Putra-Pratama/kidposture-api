<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
    'user_id',
    'physiotherapist_id',
    'plan', 'status', 'midtrans_order_id',
    'midtrans_transaction_id', 'midtrans_payment_type',
    'amount', 'started_at', 'expired_at', 'midtrans_payload',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expired_at' => 'datetime',
        'midtrans_payload' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function physiotherapist()
    {
        return $this->belongsTo(Physiotherapist::class);
    }
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expired_at > now();
    }
}
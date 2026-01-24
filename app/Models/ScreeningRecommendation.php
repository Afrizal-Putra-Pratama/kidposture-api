<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScreeningRecommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'screening_id',
        'physio_id',
        'type',
        'title',
        'content',
        'media_url',
    ];

    // Relasi ke Screening
    public function screening()
    {
        return $this->belongsTo(Screening::class);
    }

    // Relasi ke User (fisioterapis)
    public function physio()
    {
        return $this->belongsTo(User::class, 'physio_id');
    }
}

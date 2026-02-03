<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Screening extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_id',
        'user_id',
        'score',
        'category',
        'metrics',
        'summary',
        'is_multi_view',
        'total_views',
        'physiotherapist_id',
        'referral_status',
    ];

    protected $casts = [
        'metrics' => 'array',
    ];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function images()
    {
        return $this->hasMany(ScreeningImage::class);
    }

    // Relasi ke rekomendasi manual fisioterapis
    public function manualRecommendations()
    {
        return $this->hasMany(ScreeningRecommendation::class);
    }

    // Relasi ke fisioterapis yang dipilih orang tua
    public function physiotherapist()
    {
        return $this->belongsTo(Physiotherapist::class, 'physiotherapist_id');
    }
}

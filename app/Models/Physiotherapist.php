<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Physiotherapist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'clinic_name',
        'city',
        'address',
        'latitude',
        'longitude',
        'specialty',
        'bio',
        'photo',
        'practice_hours',
        'consultation_fee',
        'is_accepting_consultations',
        'status',
        'certificate_path',
        'verified_at',
        'is_verified',
        'is_active',
    ];

    protected $casts = [
        'is_accepting_consultations' => 'boolean',
        'practice_hours'             => 'array',
        'verified_at'                => 'datetime',
        'is_verified'                => 'boolean',
        'is_active'                  => 'boolean',
    ];

    protected $appends = [
        'photo_url',
        'certificate_url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function screenings()
    {
        return $this->hasMany(Screening::class, 'physiotherapist_id');
    }

    // ✅ URL foto
    public function getPhotoUrlAttribute()
    {
        if (!$this->photo) {
            return null;
        }

        return asset('storage/'.$this->photo);
    }

    // ✅ URL sertifikat
    public function getCertificateUrlAttribute()
    {
        if (!$this->certificate_path) {
            return null;
        }

        return asset('storage/'.$this->certificate_path);
    }
}

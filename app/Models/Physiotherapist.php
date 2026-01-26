<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Physiotherapist extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'clinic_name',
        'city',
        'specialty',
        'experience_years',
        'phone',
        'email',
        'photo_url',
        'bio_short',
        'is_accepting_consultations',

        // field tambahan
        'certificate_path',
        'is_verified',
        'is_active',
    ];

    protected $casts = [
        'is_accepting_consultations' => 'boolean',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'experience_years' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // URL foto profil (kalau nanti ganti ke storage path bisa diubah di sini)
    public function getPhotoUrlAttribute($value): ?string
    {
        return $value ?: null;
    }

    // URL sertifikat (untuk admin; asumsikan disimpan di storage/public)
    public function getCertificateUrlAttribute(): ?string
    {
        return $this->certificate_path
            ? asset('storage/' . $this->certificate_path)
            : null;
    }
}

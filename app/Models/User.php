<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // ✅ Konstanta role
    const ROLE_PARENT = 'parent';
    const ROLE_PHYSIO = 'physio';
    const ROLE_ADMIN  = 'admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_premium',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_premium'        => 'boolean',
    ];

    // =========================================
    // RELASI DATABASE
    // =========================================

    /**
     * Relasi ke Children (1 User memiliki banyak Anak)
     */
    public function children()
    {
        return $this->hasMany(Child::class);
    }

    /**
     * Relasi ke Physiotherapist (1 user = 1 profil fisio)
     */
    public function physiotherapist()
    {
        return $this->hasOne(Physiotherapist::class, 'user_id');
    }

    /**
     * Relasi ke Screenings (Melalui tabel Children)
     * Mengambil semua data screening dari semua anak milik user ini.
     * Catatan: Ubah menjadi $this->hasMany(Screening::class) jika di tabel 
     * screenings Anda menggunakan kolom 'user_id' secara langsung.
     */
    public function screenings()
    {
        return $this->hasManyThrough(Screening::class, Child::class);
    }

    // =========================================
    // HELPER METHODS
    // =========================================

    public function isParent(): bool
    {
        return $this->role === self::ROLE_PARENT;
    }

    public function isPhysio(): bool
    {
        return $this->role === self::ROLE_PHYSIO;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }
}
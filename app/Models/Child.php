<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Child extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'birth_date',
        'gender',
        'weight',
        'height',
    ];

    protected $appends = ['age_years'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function screenings()
    {
        return $this->hasMany(Screening::class);
    }

    // ✅ RELASI BUKAN ACCESSOR
    public function latestScreening()
    {
        return $this->hasOne(Screening::class)->latestOfMany();
    }

    // Accessor untuk umur
    public function getAgeYearsAttribute()
    {
        if (!$this->birth_date) {
            return null;
        }
        return Carbon::parse($this->birth_date)->age;
    }
}

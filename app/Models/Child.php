<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    public function parent()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function screenings()
    {
    return $this->hasMany(Screening::class);
    }

}

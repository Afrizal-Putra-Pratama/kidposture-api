<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScreeningImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'screening_id',
        'type',
        'path',
        'processed_path',
        'recommendations',  // ✅ HARUS ADA
    ];

    protected $casts = [
        'recommendations' => 'array',  // ✅ HARUS ADA (auto JSON decode)
    ];

    public function screening()
    {
        return $this->belongsTo(Screening::class);
    }
}

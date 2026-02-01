<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'user_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'thumbnail',   // simpan path "articles/xxx.jpg"
        'read_time',
        'is_published',
        'published_at',
        'views',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    // otomatis ikut di JSON
    protected $appends = ['thumbnail_url'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($article) {
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title);
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(ArticleCategory::class, 'category_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Accessor: full URL thumbnail.
     */
    public function getThumbnailUrlAttribute()
    {
        if (!$this->thumbnail) {
            return null;
        }

        // kalau sudah http/https, langsung pakai
        if (Str::startsWith($this->thumbnail, ['http://', 'https://'])) {
            return $this->thumbnail;
        }

        // kalau cuma "articles/xxx.jpg"
        return Storage::url($this->thumbnail); // /storage/articles/xxx.jpg
    }
}

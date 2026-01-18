<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'order',
    ];

    public function articles()
    {
        return $this->hasMany(Article::class, 'category_id');
    }

    public function publishedArticles()
    {
        return $this->hasMany(Article::class, 'category_id')
            ->where('is_published', true)
            ->orderBy('published_at', 'desc');
    }
}

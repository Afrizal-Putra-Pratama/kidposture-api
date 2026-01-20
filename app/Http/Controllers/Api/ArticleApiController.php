<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::with(['category', 'author'])
            ->where('is_published', true)
            ->latest('published_at');

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        $articles = $query->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $articles->map(function($article) {
                return [
                    'id' => $article->id,
                    'title' => $article->title,
                    'slug' => $article->slug,
                    'excerpt' => $article->excerpt,
                    'thumbnail' => $article->thumbnail ? asset('storage/' . $article->thumbnail) : null,
                    'read_time' => $article->read_time,
                    'views' => $article->views,
                    'published_at' => $article->published_at->format('d M Y'),
                    'category' => [
                        'id' => $article->category->id,
                        'name' => $article->category->name,
                        'slug' => $article->category->slug,
                        'icon' => $article->category->icon,
                    ],
                    'author' => [
                        'id' => $article->author->id,
                        'name' => $article->author->name,
                    ]
                ];
            }),
            'pagination' => [
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
            ]
        ]);
    }

    public function show($slug)
    {
        $article = Article::with(['category', 'author'])
            ->where('slug', $slug)
            ->where('is_published', true)
            ->first();

        if (!$article) {
            return response()->json([
                'success' => false,
                'message' => 'Article not found'
            ], 404);
        }

        // Increment views
        $article->increment('views');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt,
                'content' => $article->content,
                'thumbnail' => $article->thumbnail ? asset('storage/' . $article->thumbnail) : null,
                'read_time' => $article->read_time,
                'views' => $article->views,
                'published_at' => $article->published_at->format('d M Y, H:i'),
                'category' => [
                    'id' => $article->category->id,
                    'name' => $article->category->name,
                    'slug' => $article->category->slug,
                    'icon' => $article->category->icon,
                ],
                'author' => [
                    'id' => $article->author->id,
                    'name' => $article->author->name,
                ]
            ]
        ]);
    }
}

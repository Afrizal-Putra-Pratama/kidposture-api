<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleApiController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 12);

        $query = Article::with(['category', 'author'])
            ->where('is_published', true)
            ->orderByDesc('published_at')
            ->orderByDesc('created_at');

        // filter kategori
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        $paginator = $query->paginate($perPage);

        $data = $paginator->getCollection()->map(function (Article $article) {
            return [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt,
                'thumbnail' => $article->thumbnail_url, // ← pakai accessor (full URL bersih)
                'read_time' => $article->read_time,
                'views' => $article->views,
                'published_at' => $article->published_at
                    ? $article->published_at->format('d M Y')
                    : null,
                'category' => $article->category ? [
                    'id' => $article->category->id,
                    'name' => $article->category->name,
                    'slug' => $article->category->slug,
                    'icon' => $article->category->icon,
                ] : null,
                'author' => $article->author ? [
                    'id' => $article->author->id,
                    'name' => $article->author->name,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function show($slug)
    {
        $article = Article::with(['category', 'author'])
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $article->increment('views');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt,
                'content' => $article->content,
                'thumbnail' => $article->thumbnail_url,
                'read_time' => $article->read_time,
                'views' => $article->views,
                'published_at' => $article->published_at
                    ? $article->published_at->format('d M Y')
                    : null,
                'category' => $article->category ? [
                    'id' => $article->category->id,
                    'name' => $article->category->name,
                    'slug' => $article->category->slug,
                    'icon' => $article->category->icon,
                ] : null,
                'author' => $article->author ? [
                    'id' => $article->author->id,
                    'name' => $article->author->name,
                ] : null,
            ],
        ]);
    }
}

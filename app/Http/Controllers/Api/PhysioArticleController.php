<?php
// app/Http/Controllers/Api/PhysioArticleController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PhysioArticleController extends Controller
{
    public function index()
    {
        $articles = Article::with('category')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        // gunakan accessor thumbnail_url sebagai thumbnail di API ini
        $mapped = $articles->map(function ($article) {
            return [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt,
                'thumbnail' => $article->thumbnail_url,
                'read_time' => $article->read_time,
                'views' => $article->views,
                'published_at' => $article->published_at,
                'category' => $article->category,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $mapped,
        ]);
    }

    public function show($id)
    {
        $article = Article::with('category')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

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
                'published_at' => $article->published_at,
                'category' => $article->category,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:articles,slug',
            'category_id' => 'required|exists:article_categories,id',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'published_at' => 'nullable|date',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['slug'] = Str::slug($validated['slug']);

        $wordCount = str_word_count(strip_tags($validated['content']));
        $validated['read_time'] = max(1, round($wordCount / 200));
        $validated['is_published'] = !empty($validated['published_at']);

        if ($request->hasFile('thumbnail')) {
    $path = $request->file('thumbnail')->store('articles', 'public');
    $validated['thumbnail'] = $path; // simpan path
}

        $article = Article::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Artikel berhasil dibuat.',
            'data' => $article->load('category'),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $article = Article::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:articles,slug,' . $id,
            'category_id' => 'required|exists:article_categories,id',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'published_at' => 'nullable|date',
        ]);

        $validated['slug'] = Str::slug($validated['slug']);

        $wordCount = str_word_count(strip_tags($validated['content']));
        $validated['read_time'] = max(1, round($wordCount / 200));
        $validated['is_published'] = !empty($validated['published_at']);

        if ($request->hasFile('thumbnail')) {
    if ($article->thumbnail) {
        Storage::disk('public')->delete($article->thumbnail);
    }
    $path = $request->file('thumbnail')->store('articles', 'public');
    $validated['thumbnail'] = $path;
}

        $article->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Artikel berhasil diperbarui.',
            'data' => $article->load('category'),
        ]);
    }

    public function destroy($id)
    {
        $article = Article::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($article->thumbnail) {
            Storage::disk('public')->delete($article->thumbnail);
        }

        $article->delete();

        return response()->json([
            'success' => true,
            'message' => 'Artikel berhasil dihapus.',
        ]);
    }
}

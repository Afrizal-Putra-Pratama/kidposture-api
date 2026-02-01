<?php
// app/Http/Controllers/PhysioArticleController.php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PhysioArticleController extends Controller
{
    /**
     * Get all articles by logged-in physiotherapist
     */
    public function index()
    {
        $articles = Article::with('category')
            ->where('author_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $articles,
        ]);
    }

    /**
     * Get single article
     */
    public function show($id)
    {
        $article = Article::with('category')
            ->where('id', $id)
            ->where('author_id', Auth::id())
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $article,
        ]);
    }

    /**
     * Create new article
     */
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

        $validated['author_id'] = Auth::id();
        $validated['slug'] = Str::slug($validated['slug']);

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('articles', 'public');
            $validated['thumbnail'] = Storage::url($path);
        }

        $article = Article::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Artikel berhasil dibuat.',
            'data' => $article->load('category'),
        ], 201);
    }

    /**
     * Update article
     */
    public function update(Request $request, $id)
    {
        $article = Article::where('id', $id)
            ->where('author_id', Auth::id())
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

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($article->thumbnail) {
                $oldPath = str_replace('/storage', 'public', $article->thumbnail);
                Storage::delete($oldPath);
            }

            $path = $request->file('thumbnail')->store('articles', 'public');
            $validated['thumbnail'] = Storage::url($path);
        }

        $article->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Artikel berhasil diperbarui.',
            'data' => $article->load('category'),
        ]);
    }

    /**
     * Delete article
     */
    public function destroy($id)
    {
        $article = Article::where('id', $id)
            ->where('author_id', Auth::id())
            ->firstOrFail();

        // Delete thumbnail
        if ($article->thumbnail) {
            $path = str_replace('/storage', 'public', $article->thumbnail);
            Storage::delete($path);
        }

        $article->delete();

        return response()->json([
            'success' => true,
            'message' => 'Artikel berhasil dihapus.',
        ]);
    }
}

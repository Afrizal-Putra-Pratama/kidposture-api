<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::with(['category', 'author'])
            ->latest()
            ->paginate(20);

        return view('admin.articles.index', compact('articles'));
    }

    public function create()
    {
        $categories = ArticleCategory::orderBy('order')->get();
        return view('admin.articles.create', compact('categories'));
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|max:255',
        'category_id' => 'required|exists:article_categories,id',
        'excerpt' => 'nullable|max:500',
        'content' => 'required',
        'thumbnail' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
        'read_time' => 'nullable|integer|min:1',
    ]);

    $data = [
        'title' => $request->title,
        'category_id' => $request->category_id,
        'excerpt' => $request->excerpt,
        'content' => $request->content,
        'read_time' => $request->read_time ?? 5,
        'user_id' => auth()->id(),
        'slug' => Str::slug($request->title),
        'is_published' => $request->has('is_published') ? true : false,
        'views' => 0
    ];
    
    if ($data['is_published']) {
        $data['published_at'] = now();
    }

    // Handle thumbnail upload - FIXED VERSION
    if ($request->hasFile('thumbnail')) {
        $file = $request->file('thumbnail');
        
        if ($file->isValid()) {
            // Generate unique filename
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . uniqid() . '.' . $extension;
            
            // Move file to public/storage/articles
            $file->move(public_path('storage/articles'), $filename);
            
            // Save relative path
            $data['thumbnail'] = 'articles/' . $filename;
        }
    }

    Article::create($data);

    return redirect()->route('admin.articles.index')
        ->with('success', 'Artikel berhasil dibuat!');
}


    public function show(Article $article)
    {
        $article->load('category', 'author');
        return view('admin.articles.show', compact('article'));
    }

    public function edit(Article $article)
    {
        $categories = ArticleCategory::orderBy('order')->get();
        return view('admin.articles.edit', compact('article', 'categories'));
    }

    public function update(Request $request, Article $article)
{
    $validated = $request->validate([
        'title' => 'required|max:255',
        'category_id' => 'required|exists:article_categories,id',
        'excerpt' => 'nullable|max:500',
        'content' => 'required',
        'thumbnail' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
        'read_time' => 'nullable|integer|min:1',
    ]);

    $data = [
        'title' => $request->title,
        'category_id' => $request->category_id,
        'excerpt' => $request->excerpt,
        'content' => $request->content,
        'read_time' => $request->read_time ?? 5,
        'slug' => Str::slug($request->title),
        'is_published' => $request->has('is_published') ? true : false,
    ];
    
    if ($data['is_published'] && !$article->published_at) {
        $data['published_at'] = now();
    }

    // Handle thumbnail upload - FIXED VERSION
    if ($request->hasFile('thumbnail')) {
        $file = $request->file('thumbnail');
        
        if ($file->isValid()) {
            // Delete old thumbnail
            if ($article->thumbnail) {
                $oldPath = public_path('storage/' . $article->thumbnail);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            
            // Generate unique filename
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . uniqid() . '.' . $extension;
            
            // Move file
            $file->move(public_path('storage/articles'), $filename);
            
            // Save relative path
            $data['thumbnail'] = 'articles/' . $filename;
        }
    }

    $article->update($data);

    return redirect()->route('admin.articles.index')
        ->with('success', 'Artikel berhasil diupdate!');
}


    public function destroy(Article $article)
{
    // Delete thumbnail
    if ($article->thumbnail) {
        $path = public_path('storage/' . $article->thumbnail);
        if (file_exists($path)) {
            unlink($path);
        }
    }

    $article->delete();

    return redirect()->route('admin.articles.index')
        ->with('success', 'Artikel berhasil dihapus!');
}

}

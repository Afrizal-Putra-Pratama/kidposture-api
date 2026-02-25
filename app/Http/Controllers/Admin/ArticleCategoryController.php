<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArticleCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleCategoryController extends Controller
{
    /**
     * Menampilkan daftar kategori
     */
    public function index()
    {
        // Mengambil kategori beserta jumlah artikel di dalamnya
        $categories = ArticleCategory::withCount('articles')->latest()->get();
        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Menyimpan data kategori baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:article_categories,name',
        ]);

        ArticleCategory::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'order' => 0
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil ditambahkan!');
    }

    /**
     * Menyimpan perubahan data kategori
     */
    public function update(Request $request, ArticleCategory $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:article_categories,name,' . $category->id,
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil diperbarui!');
    }

    /**
     * Menghapus kategori dengan validasi proteksi relasi
     */
    public function destroy(ArticleCategory $category)
    {
        // Proteksi Tambahan di Backend: Tolak jika ada artikel
        if ($category->articles()->count() > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Gagal! Kategori ini tidak bisa dihapus karena masih digunakan.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil dihapus!');
    }
}
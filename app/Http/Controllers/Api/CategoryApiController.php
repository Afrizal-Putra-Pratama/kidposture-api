<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ArticleCategory;

class CategoryApiController extends Controller
{
    public function index()
    {
        $categories = ArticleCategory::withCount(['articles' => function($query) {
            $query->where('is_published', true);
        }])
        ->orderBy('order')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $categories->map(function($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'icon' => $category->icon,
                    'description' => $category->description,
                    'articles_count' => $category->articles_count,
                ];
            })
        ]);
    }
}

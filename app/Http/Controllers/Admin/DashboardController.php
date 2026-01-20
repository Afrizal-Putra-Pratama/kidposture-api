<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\User;
use App\Models\Child;
use App\Models\Screening;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'users' => User::count(),
            'children' => Child::count(),
            'screenings' => Screening::count(),
            'articles' => Article::count(),
            'published_articles' => Article::where('is_published', true)->count(),
        ];

        $recent_articles = Article::with('category', 'author')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recent_articles'));
    }
}

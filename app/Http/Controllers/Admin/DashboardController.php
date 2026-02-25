<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\User;
use App\Models\Child;
use App\Models\Screening;
use App\Models\Physiotherapist;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Data Statistik
        $stats = [
            'users' => User::count(),
            'children' => Child::count(),
            'screenings' => Screening::count(),
            'articles' => Article::count(),
            'published_articles' => Article::where('is_published', true)->count(),
        ];

        // 2. Logika Sorting & Paginate untuk RECENT ARTICLES
        $sortArticle = $request->get('sort_article', 'date'); // Default sort by date
        $dirArticle = $request->get('dir_article', 'desc');   // Default direction descending (terbaru)

        $articleQuery = Article::with('category', 'author');
        
        if ($sortArticle === 'title') {
            $articleQuery->orderBy('title', $dirArticle);
        } else {
            $articleQuery->orderBy('created_at', $dirArticle);
        }
        
        $recent_articles = $articleQuery->paginate(5, ['*'], 'article_page');

        // 3. Logika Sorting & Paginate untuk RECENT PHYSIOTHERAPISTS
        $sortPhysio = $request->get('sort_physio', 'date'); // Default sort by date
        $dirPhysio = $request->get('dir_physio', 'desc');   // Default direction descending (terbaru)

        $physioQuery = Physiotherapist::query();
        
        if ($sortPhysio === 'name') {
            $physioQuery->orderBy('name', $dirPhysio);
        } else {
            $physioQuery->orderBy('created_at', $dirPhysio);
        }
        
        $recent_physios = $physioQuery->paginate(5, ['*'], 'physio_page');

        return view('admin.dashboard', compact('stats', 'recent_articles', 'recent_physios'));
    }
}
@extends('admin.layouts.admin')


@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-speedometer2"></i> Dashboard</h2>
</div>

<div class="row">
    <!-- Stats Cards -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-start border-primary border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">Total Users</div>
                        <div class="h3 mb-0">{{ $stats['users'] }}</div>
                    </div>
                    <div class="text-primary">
                        <i class="bi bi-people fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-start border-success border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">Total Children</div>
                        <div class="h3 mb-0">{{ $stats['children'] }}</div>
                    </div>
                    <div class="text-success">
                        <i class="bi bi-person-hearts fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-start border-warning border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">Total Screenings</div>
                        <div class="h3 mb-0">{{ $stats['screenings'] }}</div>
                    </div>
                    <div class="text-warning">
                        <i class="bi bi-clipboard2-pulse fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-start border-info border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">Published Articles</div>
                        <div class="h3 mb-0">{{ $stats['published_articles'] }}/{{ $stats['articles'] }}</div>
                    </div>
                    <div class="text-info">
                        <i class="bi bi-file-earmark-text fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Articles -->
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Articles</h5>
        <a href="{{ route('admin.articles.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-circle"></i> New Article
        </a>
    </div>
    <div class="card-body">
        @if($recent_articles->isEmpty())
            <p class="text-muted text-center py-4">No articles yet. Create your first article!</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Author</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recent_articles as $article)
                        <tr>
                            <td>
                                <a href="{{ route('admin.articles.show', $article) }}" class="text-decoration-none">
                                    {{ $article->title }}
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ $article->category->icon }} {{ $article->category->name }}
                                </span>
                            </td>
                            <td>{{ $article->author->name }}</td>
                            <td>
                                @if($article->is_published)
                                    <span class="badge bg-success">Published</span>
                                @else
                                    <span class="badge bg-warning">Draft</span>
                                @endif
                            </td>
                            <td>{{ $article->created_at->format('d M Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

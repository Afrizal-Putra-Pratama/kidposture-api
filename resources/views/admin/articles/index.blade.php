@extends('layouts.admin')

@section('title', 'Articles')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-file-earmark-text"></i> Articles</h2>
    <a href="{{ route('admin.articles.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Create Article
    </a>
</div>

<div class="card">
    <div class="card-body">
        @if($articles->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <p class="text-muted mt-3">No articles yet. Create your first article!</p>
                <a href="{{ route('admin.articles.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Create Article
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="60">ID</th>
                            <th>Thumbnail</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Author</th>
                            <th width="100" class="text-center">Views</th>
                            <th width="100" class="text-center">Status</th>
                            <th width="180" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($articles as $article)
                        <tr>
                            <td>{{ $article->id }}</td>
                            <td>
                                @if($article->thumbnail)
                                    <img src="{{ Storage::url($article->thumbnail) }}" 
                                         alt="Thumbnail" 
                                         class="rounded"
                                         style="width: 60px; height: 60px; object-fit: cover;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                         style="width: 60px; height: 60px;">
                                        <i class="bi bi-image text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.articles.show', $article) }}" 
                                   class="text-decoration-none fw-semibold">
                                    {{ Str::limit($article->title, 50) }}
                                </a>
                                <br>
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i> {{ $article->read_time }} min read
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ $article->category->icon }} {{ $article->category->name }}
                                </span>
                            </td>
                            <td>{{ $article->author->name }}</td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ $article->views }}</span>
                            </td>
                            <td class="text-center">
                                @if($article->is_published)
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Published
                                    </span>
                                @else
                                    <span class="badge bg-warning">
                                        <i class="bi bi-pencil"></i> Draft
                                    </span>
                                @endif
                            </td>
                            <td class="text-center table-actions">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.articles.show', $article) }}" 
                                       class="btn btn-sm btn-info" 
                                       title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.articles.edit', $article) }}" 
                                       class="btn btn-sm btn-warning" 
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.articles.destroy', $article) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this article?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $articles->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

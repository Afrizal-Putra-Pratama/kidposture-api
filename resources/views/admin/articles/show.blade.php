@extends('admin.layouts.admin')


@section('title', $article->title)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-file-earmark-text"></i> Article Details</h2>
    <div>
        <a href="{{ route('admin.articles.edit', $article) }}" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <a href="{{ route('admin.articles.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            @if($article->thumbnail)
                <img src="{{ asset('storage/' . $article->thumbnail) }}" 
                     class="card-img-top" 
                     alt="{{ $article->title }}"
                     style="max-height: 400px; object-fit: cover;">
            @endif
            
            <div class="card-body">
                <!-- Title -->
                <h1 class="card-title mb-3">{{ $article->title }}</h1>

                <!-- Meta Info -->
                <div class="d-flex flex-wrap gap-3 mb-4 text-muted small">
                    <span>
                        <i class="bi bi-folder"></i> 
                        <span class="badge bg-secondary">
                            {{ $article->category->icon }} {{ $article->category->name }}
                        </span>
                    </span>
                    <span>
                        <i class="bi bi-person"></i> {{ $article->author->name }}
                    </span>
                    <span>
                        <i class="bi bi-clock"></i> {{ $article->read_time }} min read
                    </span>
                    <span>
                        <i class="bi bi-eye"></i> {{ $article->views }} views
                    </span>
                    <span>
                        <i class="bi bi-calendar"></i> {{ $article->created_at->format('d M Y') }}
                    </span>
                </div>

                <!-- Excerpt -->
                @if($article->excerpt)
                    <div class="alert alert-light border">
                        <strong>Excerpt:</strong> {{ $article->excerpt }}
                    </div>
                @endif

                <hr>

                <!-- Content -->
                <div class="article-content">
                    {!! $article->content !!}
                </div>
            </div>

            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        @if($article->is_published)
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Published
                            </span>
                            @if($article->published_at)
                                <small class="text-muted ms-2">
                                    on {{ $article->published_at->format('d M Y, H:i') }}
                                </small>
                            @endif
                        @else
                            <span class="badge bg-warning">
                                <i class="bi bi-pencil"></i> Draft
                            </span>
                        @endif
                    </div>
                    
                    <div>
                        <a href="{{ route('admin.articles.edit', $article) }}" class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <form action="{{ route('admin.articles.destroy', $article) }}" 
                              method="POST" 
                              class="d-inline"
                              onsubmit="return confirm('Are you sure you want to delete this article?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Quick Stats -->
        <div class="card mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Statistics</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Total Views</span>
                        <strong>{{ $article->views }}</strong>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Read Time</span>
                        <strong>{{ $article->read_time }} min</strong>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Category</span>
                        <span class="badge bg-secondary">
                            {{ $article->category->icon }} {{ $article->category->name }}
                        </span>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Status</span>
                        @if($article->is_published)
                            <span class="badge bg-success">Published</span>
                        @else
                            <span class="badge bg-warning">Draft</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Article Info -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Article Info</h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <small class="text-muted">Author</small>
                    <div><strong>{{ $article->author->name }}</strong></div>
                </div>
                <hr>
                <div class="mb-2">
                    <small class="text-muted">Created</small>
                    <div>{{ $article->created_at->format('d M Y, H:i') }}</div>
                    <small class="text-muted">({{ $article->created_at->diffForHumans() }})</small>
                </div>
                <hr>
                <div class="mb-2">
                    <small class="text-muted">Last Updated</small>
                    <div>{{ $article->updated_at->format('d M Y, H:i') }}</div>
                    <small class="text-muted">({{ $article->updated_at->diffForHumans() }})</small>
                </div>
                @if($article->published_at)
                    <hr>
                    <div>
                        <small class="text-muted">Published</small>
                        <div>{{ $article->published_at->format('d M Y, H:i') }}</div>
                        <small class="text-muted">({{ $article->published_at->diffForHumans() }})</small>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .article-content {
        font-size: 1.1rem;
        line-height: 1.8;
    }
    .article-content img {
        max-width: 100%;
        height: auto;
        border-radius: 0.5rem;
        margin: 1rem 0;
    }
    .article-content h1, .article-content h2, .article-content h3 {
        margin-top: 1.5rem;
        margin-bottom: 1rem;
    }
    .article-content p {
        margin-bottom: 1rem;
    }
</style>
@endpush

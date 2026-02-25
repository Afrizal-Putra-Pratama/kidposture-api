@extends('admin.layouts.admin')

@section('title', $article->title)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/article-show.css') }}">
@endpush

@section('content')
<div class="page-header">
    <h2 class="page-title"><i class="bi bi-file-earmark-text"></i> Article Details</h2>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.articles.edit', $article) }}" class="btn btn--warning">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <a href="{{ route('admin.articles.index') }}" class="btn btn--secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<div class="content-grid">
    <div class="content-main">
        <div class="card" style="padding: 0; overflow: hidden;">
            
            @if($article->thumbnail)
                <img src="{{ asset('storage/' . $article->thumbnail) }}" 
                     alt="{{ $article->title }}"
                     class="article-hero-img">
            @endif
            
            <div class="article-body-wrapper">
                <h1 class="article-title-heading">
                    {{ $article->title }}
                </h1>

                <div class="article-meta-row">
                    <span class="badge badge--secondary">
                        {{ $article->category->name ?? 'Uncategorized' }}
                    </span>
                    <span class="d-flex align-center gap-2"><i class="bi bi-person"></i> {{ $article->author->name }}</span>
                    <span class="d-flex align-center gap-2"><i class="bi bi-clock"></i> {{ $article->read_time }} min read</span>
                    <span class="d-flex align-center gap-2"><i class="bi bi-eye"></i> {{ $article->views }} views</span>
                    <span class="d-flex align-center gap-2"><i class="bi bi-calendar"></i> {{ $article->created_at->format('d M Y') }}</span>
                </div>

                @if($article->excerpt)
                    <div class="article-excerpt-box">
                        {{ $article->excerpt }}
                    </div>
                @endif

                <div class="article-content">
                    {!! $article->content !!}
                </div>
            </div>

            <div class="article-action-footer">
                <div>
                    @if($article->is_published)
                        <span class="badge badge--success"><i class="bi bi-check-circle"></i> Published</span>
                        @if($article->published_at)
                            <span style="font-size: 0.85rem; color: var(--gray-500); margin-left: 0.5rem;">
                                on {{ $article->published_at->format('d M Y, H:i') }}
                            </span>
                        @endif
                    @else
                        <span class="badge badge--warning"><i class="bi bi-pencil"></i> Draft</span>
                    @endif
                </div>
                
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.articles.edit', $article) }}" class="btn btn--warning btn--sm">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <button type="button" class="btn btn--danger btn--sm" onclick="openDeleteModal('{{ route('admin.articles.destroy', $article) }}', '{{ Str::limit($article->title, 30) }}')">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="content-sidebar">
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card__header">
                <i class="bi bi-graph-up"></i> Statistics
            </div>
            <div class="sidebar-info-list">
                <div class="sidebar-info-row">
                    <span class="sidebar-info-label">Total Views</span>
                    <span class="sidebar-info-value" style="font-size: 1.1rem;">{{ $article->views }}</span>
                </div>
                <hr class="sidebar-divider">
                
                <div class="sidebar-info-row">
                    <span class="sidebar-info-label">Read Time</span>
                    <span class="sidebar-info-value">{{ $article->read_time }} min</span>
                </div>
                <hr class="sidebar-divider">
                
                <div class="sidebar-info-row">
                    <span class="sidebar-info-label">Category</span>
                    <span class="badge badge--secondary">{{ $article->category->name ?? 'None' }}</span>
                </div>
                <hr class="sidebar-divider">
                
                <div class="sidebar-info-row">
                    <span class="sidebar-info-label">Status</span>
                    @if($article->is_published)
                        <span class="badge badge--success">Published</span>
                    @else
                        <span class="badge badge--warning">Draft</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card__header">
                <i class="bi bi-info-circle"></i> Timeline Info
            </div>
            <div class="sidebar-info-list">
                <div>
                    <span class="form-text-muted mt-0">Author</span>
                    <span class="sidebar-info-value" style="display: block; margin-top: 0.2rem;">{{ $article->author->name }}</span>
                </div>
                <hr class="sidebar-divider-solid">
                
                <div>
                    <span class="form-text-muted mt-0">Created</span>
                    <div class="sidebar-info-value" style="margin-top: 0.2rem;">{{ $article->created_at->format('d M Y, H:i') }}</div>
                    <span style="color: var(--gray-400); font-size: 0.8rem;">({{ $article->created_at->diffForHumans() }})</span>
                </div>
                <hr class="sidebar-divider-solid">
                
                <div>
                    <span class="form-text-muted mt-0">Last Updated</span>
                    <div class="sidebar-info-value" style="margin-top: 0.2rem;">{{ $article->updated_at->format('d M Y, H:i') }}</div>
                    <span style="color: var(--gray-400); font-size: 0.8rem;">({{ $article->updated_at->diffForHumans() }})</span>
                </div>
                
                @if($article->published_at)
                <hr class="sidebar-divider-solid">
                <div>
                    <span class="form-text-muted mt-0">Published Date</span>
                    <div class="sidebar-info-value" style="margin-top: 0.2rem;">{{ $article->published_at->format('d M Y, H:i') }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal-overlay" id="deleteModal">
    <div class="modal-box" style="max-width: 400px; text-align: center;">
        <div class="modal-body" style="padding: 2.5rem 1.5rem 1.5rem;">
            <i class="bi bi-exclamation-circle text-danger" style="font-size: 4rem; color: #ef4444; margin-bottom: 1rem; display: block;"></i>
            <h3 style="font-size: 1.25rem; color: var(--gray-900); margin-bottom: 0.5rem;">Hapus Artikel?</h3>
            <p style="color: var(--gray-600); margin-bottom: 0;">
                Apakah Anda yakin ingin menghapus artikel <br><strong id="deleteArticleTitle" style="color: var(--gray-900);"></strong>?
            </p>
        </div>
        <form id="deleteForm" method="POST" class="modal-footer" style="justify-content: center; background: transparent; border-top: none; padding-top: 0;">
            @csrf
            @method('DELETE')
            <button type="button" class="btn btn--secondary" onclick="closeModal('deleteModal')">Batal</button>
            <button type="submit" class="btn btn--danger">Ya, Hapus!</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openDeleteModal(actionRoute, title) {
        document.getElementById('deleteForm').action = actionRoute;
        document.getElementById('deleteArticleTitle').textContent = '"' + title + '"';
        document.getElementById('deleteModal').classList.add('show');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('show');
    }

    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.classList.remove('show');
        }
    }
</script>
@endpush
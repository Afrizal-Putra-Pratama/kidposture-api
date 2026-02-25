@extends('admin.layouts.admin')

@section('title', 'Articles')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/articles.css') }}">
@endpush

@section('content')
<div class="page-header">
    <h2 class="page-title"><i class="bi bi-file-earmark-text"></i> Articles</h2>
    <a href="{{ route('admin.articles.create') }}" class="btn btn--primary">
        <i class="bi bi-plus-circle"></i> Create Article
    </a>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    @if($articles->isEmpty())
        <div class="empty-state" style="padding: 4rem 2rem;">
            <i class="bi bi-inbox" style="font-size: 3rem; color: var(--gray-300); margin-bottom: 1rem; display: block;"></i>
            <p style="margin-bottom: 1.5rem; color: var(--gray-500);">No articles yet. Create your first article!</p>
            <a href="{{ route('admin.articles.create') }}" class="btn btn--primary">
                <i class="bi bi-plus-circle"></i> Create Article
            </a>
        </div>
    @else
        <div class="table-wrapper" style="border: none; border-radius: 0;">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th width="70">Thumb</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Author</th>
                        <th class="text-center">Views</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($articles as $article)
                    <tr>
                        <td class="td-nowrap">
                            @if($article->thumbnail)
                                <img src="{{ Storage::url($article->thumbnail) }}" 
                                     alt="Thumbnail" 
                                     class="rounded-img"
                                     style="width: 50px; height: 50px; object-fit: cover;">
                            @else
                                <div class="rounded-img d-flex align-center justify-center" style="width: 50px; height: 50px; background: var(--gray-100); justify-content: center;">
                                    <i class="bi bi-image" style="color: var(--gray-400);"></i>
                                </div>
                            @endif
                        </td>
                        
                        <td class="td-wrap">
                            <a href="{{ route('admin.articles.show', $article) }}" class="article-title-link">
                                {{ $article->title }}
                            </a>
                            <span class="form-text-muted mt-0">
                                <i class="bi bi-clock"></i> {{ $article->read_time }} min read
                            </span>
                        </td>
                        
                        <td class="td-nowrap">
                            <span class="badge badge--secondary">
                                {{ $article->category->name ?? 'Uncategorized' }}
                            </span>
                        </td>
                        <td class="td-nowrap">{{ $article->author->name }}</td>
                        <td class="text-center td-nowrap">
                            <span class="badge badge--info">{{ $article->views }}</span>
                        </td>
                        <td class="text-center td-nowrap">
                            @if($article->is_published)
                                <span class="badge badge--success"><i class="bi bi-check-circle"></i> Published</span>
                            @else
                                <span class="badge badge--warning"><i class="bi bi-pencil"></i> Draft</span>
                            @endif
                        </td>
                        <td class="text-center td-nowrap">
                            <div class="d-flex gap-2" style="justify-content: center;">
                                <a href="{{ route('admin.articles.show', $article) }}" class="btn btn--info btn--sm" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.articles.edit', $article) }}" class="btn btn--warning btn--sm" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn--danger btn--sm" title="Delete"
                                        onclick="openDeleteModal('{{ route('admin.articles.destroy', $article) }}', '{{ Str::limit($article->title, 30) }}')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pagination-container">
            {{ $articles->links() }}
        </div>
    @endif
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
    // Fungsi Modal Hapus
    function openDeleteModal(actionRoute, title) {
        document.getElementById('deleteForm').action = actionRoute;
        document.getElementById('deleteArticleTitle').textContent = '"' + title + '"';
        document.getElementById('deleteModal').classList.add('show');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('show');
    }

    // Tutup modal jika klik luar box
    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.classList.remove('show');
        }
    }
</script>
@endpush
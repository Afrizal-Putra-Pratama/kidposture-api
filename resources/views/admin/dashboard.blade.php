@extends('admin.layouts.admin')

@section('title', 'Dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')

@php
    // Logika Toggle Artikel
    $articleTitleDir = (request('sort_article') === 'title' && request('dir_article') === 'asc') ? 'desc' : 'asc';
    $articleDateDir = (request('sort_article', 'date') === 'date' && request('dir_article', 'desc') === 'desc') ? 'asc' : 'desc';
    
    // Logika Toggle Fisioterapis
    $physioNameDir = (request('sort_physio') === 'name' && request('dir_physio') === 'asc') ? 'desc' : 'asc';
    $physioDateDir = (request('sort_physio', 'date') === 'date' && request('dir_physio', 'desc') === 'desc') ? 'asc' : 'desc';
@endphp

<div class="page-header">
    <h2 class="page-title"><i class="bi bi-speedometer2"></i> Dashboard</h2>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--blue">
            <i class="bi bi-people"></i>
        </div>
        <div class="stat-card__info">
            <span class="stat-card__label">Total Users</span>
            <span class="stat-card__value">{{ $stats['users'] ?? 0 }}</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--green">
            <i class="bi bi-person-hearts"></i>
        </div>
        <div class="stat-card__info">
            <span class="stat-card__label">Total Children</span>
            <span class="stat-card__value">{{ $stats['children'] ?? 0 }}</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--warning">
            <i class="bi bi-clipboard2-pulse"></i>
        </div>
        <div class="stat-card__info">
            <span class="stat-card__label">Total Screenings</span>
            <span class="stat-card__value">{{ $stats['screenings'] ?? 0 }}</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--info">
            <i class="bi bi-file-earmark-text"></i>
        </div>
        <div class="stat-card__info">
            <span class="stat-card__label">Published Articles</span>
            <span class="stat-card__value">{{ $stats['published_articles'] ?? 0 }}/{{ $stats['articles'] ?? 0 }}</span>
        </div>
    </div>
</div>

<div class="content-grid" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
    
    <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 0; display: flex; flex-direction: column;">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--gray-200); background: var(--white);">
            <h3 style="margin: 0; font-size: 1.1rem; font-weight: 600; color: var(--gray-900); display: flex; align-items: center; gap: 0.5rem;">
                <i class="bi bi-clock-history"></i> Recent Articles
            </h3>
            <a href="{{ route('admin.articles.create') }}" class="btn btn--primary btn--sm">
                <i class="bi bi-plus-circle"></i> Add New
            </a>
        </div>
        
        <div class="table-wrapper" style="border: none; border-radius: 0; flex: 1;">
            @if($recent_articles->isEmpty())
                <div class="empty-state" style="padding: 3rem 1rem;">
                    <p style="margin: 0;">No articles yet.</p>
                </div>
            @else
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort_article' => 'title', 'dir_article' => $articleTitleDir, 'article_page' => 1]) }}" 
                                   class="table-sort-link {{ request('sort_article') === 'title' ? 'active' : '' }}">
                                    Title 
                                    @if(request('sort_article') === 'title')
                                        <i class="bi bi-arrow-{{ request('dir_article') === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="bi bi-arrow-down-up"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Status</th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort_article' => 'date', 'dir_article' => $articleDateDir, 'article_page' => 1]) }}" 
                                   class="table-sort-link {{ request('sort_article', 'date') === 'date' ? 'active' : '' }}">
                                    Date 
                                    @if(request('sort_article', 'date') === 'date')
                                        <i class="bi bi-arrow-{{ request('dir_article', 'desc') === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="bi bi-arrow-down-up"></i>
                                    @endif
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recent_articles as $article)
                        <tr>
                            <td class="td-wrap" style="min-width: 150px;">
                                <a href="{{ route('admin.articles.show', $article) }}" style="text-decoration: none; color: var(--accent-dark); font-weight: 600; display: block;">
                                    {{ Str::limit($article->title, 40) }}
                                </a>
                                <span style="font-size: 0.8rem; color: var(--gray-500);">{{ $article->category->name ?? 'Uncategorized' }}</span>
                            </td>
                            <td class="td-nowrap">
                                @if($article->is_published)
                                    <span class="badge badge--success">Published</span>
                                @else
                                    <span class="badge badge--warning">Draft</span>
                                @endif
                            </td>
                            <td class="td-nowrap">{{ $article->created_at->format('d M Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        
        @if($recent_articles->hasPages())
            <div class="dashboard-pagination">
                {{ $recent_articles->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 0; display: flex; flex-direction: column;">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--gray-200); background: var(--white);">
            <h3 style="margin: 0; font-size: 1.1rem; font-weight: 600; color: var(--gray-900); display: flex; align-items: center; gap: 0.5rem;">
                <i class="bi bi-person-vcard"></i> Recent Physiotherapists
            </h3>
            <a href="{{ route('admin.physiotherapists.index') }}" class="btn btn--secondary btn--sm">
                <i class="bi bi-list-ul"></i> View All
            </a>
        </div>
        
        <div class="table-wrapper" style="border: none; border-radius: 0; flex: 1;">
            @if(!isset($recent_physios) || $recent_physios->isEmpty())
                <div class="empty-state" style="padding: 3rem 1rem;">
                    <p style="margin: 0;">No physiotherapists registered yet.</p>
                </div>
            @else
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort_physio' => 'name', 'dir_physio' => $physioNameDir, 'physio_page' => 1]) }}" 
                                   class="table-sort-link {{ request('sort_physio') === 'name' ? 'active' : '' }}">
                                    Name 
                                    @if(request('sort_physio') === 'name')
                                        <i class="bi bi-arrow-{{ request('dir_physio') === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="bi bi-arrow-down-up"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Verification</th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort_physio' => 'date', 'dir_physio' => $physioDateDir, 'physio_page' => 1]) }}" 
                                   class="table-sort-link {{ request('sort_physio', 'date') === 'date' ? 'active' : '' }}">
                                    Joined 
                                    @if(request('sort_physio', 'date') === 'date')
                                        <i class="bi bi-arrow-{{ request('dir_physio', 'desc') === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="bi bi-arrow-down-up"></i>
                                    @endif
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recent_physios as $physio)
                        <tr>
                            <td class="td-wrap" style="min-width: 150px;">
                                <a href="{{ route('admin.physiotherapists.show', $physio) }}" style="text-decoration: none; color: var(--gray-900); font-weight: 600; display: block;">
                                    {{ $physio->name }}
                                </a>
                                <span style="font-size: 0.8rem; color: var(--gray-500);"><i class="bi bi-geo-alt"></i> {{ $physio->city ?? '-' }}</span>
                            </td>
                            <td class="td-nowrap">
                                @if ($physio->is_verified)
                                    <span class="badge badge--success"><i class="bi bi-check-circle"></i> Verified</span>
                                @else
                                    <span class="badge badge--warning" style="background: #fef3c7; color: #d97706;"><i class="bi bi-clock"></i> Pending</span>
                                @endif
                            </td>
                            <td class="td-nowrap">{{ $physio->created_at->format('d M Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        @if(isset($recent_physios) && $recent_physios->hasPages())
            <div class="dashboard-pagination">
                {{ $recent_physios->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

</div>
@endsection
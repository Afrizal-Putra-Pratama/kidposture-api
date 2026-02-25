@extends('admin.layouts.admin')

@section('title', 'Kategori Artikel')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/category-index.css') }}">
@endpush

@section('content')
<div class="page-header">
    <h2 class="page-title"><i class="bi bi-tags"></i> Kategori Artikel</h2>
    <button type="button" class="btn btn--primary" onclick="openCreateModal()">
        <i class="bi bi-plus-circle"></i> Tambah Kategori
    </button>
</div>

@if($errors->any())
    <div class="alert alert--danger">
        <i class="bi bi-exclamation-triangle-fill alert__icon"></i>
        <span class="alert__text">Input tidak valid! {{ $errors->first() }}</span>
    </div>
@endif

<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-wrapper" style="border: none; border-radius: 0;">
        @if($categories->isEmpty())
            <div class="empty-state" style="padding: 4rem 2rem;">
                <i class="bi bi-tags" style="font-size: 3rem; color: var(--gray-300); margin-bottom: 1rem; display: block;"></i>
                <p style="margin-bottom: 1.5rem; color: var(--gray-500);">Belum ada kategori artikel.</p>
                <button type="button" class="btn btn--primary" onclick="openCreateModal()">
                    <i class="bi bi-plus-circle"></i> Tambah Kategori Pertama
                </button>
            </div>
        @else
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Nama Kategori & Slug</th>
                        <th class="text-center">Total Artikel</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $category)
                    <tr>
                        <td class="td-wrap">
                            <span class="category-name">{{ $category->name }}</span>
                            <span class="category-slug">/{{ $category->slug }}</span>
                        </td>
                        <td class="text-center td-nowrap">
                            @if($category->articles_count > 0)
                                <span class="badge badge--info">{{ $category->articles_count }} Artikel</span>
                            @else
                                <span class="badge badge--secondary">Kosong</span>
                            @endif
                        </td>
                        <td class="text-center td-nowrap">
                            <div class="d-flex gap-2" style="justify-content: center;">
                                
                                <button type="button" class="btn btn--warning btn--sm" title="Edit" 
                                        onclick="openEditModal('{{ $category->id }}', '{{ $category->name }}', '{{ route('admin.categories.update', $category) }}')">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                
                                @if($category->articles_count > 0)
                                    <button type="button" class="btn btn--danger btn--sm" disabled title="Tidak bisa dihapus karena masih ada artikel di dalamnya">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                @else
                                    <button type="button" class="btn btn--danger btn--sm" title="Hapus"
                                            onclick="openDeleteModal('{{ route('admin.categories.destroy', $category) }}', '{{ $category->name }}')">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                @endif

                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

<div class="modal-overlay" id="createModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title"><i class="bi bi-plus-circle text-primary"></i> Tambah Kategori Baru</h3>
            <button class="modal-close-btn" onclick="closeModal('createModal')"><i class="bi bi-x-lg"></i></button>
        </div>
        <form action="{{ route('admin.categories.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="create_name" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                    <input type="text" class="form-input" id="create_name" name="name" placeholder="Contoh: Latihan Postur, Nutrisi" required>
                    <span class="form-text-muted">Slug URL akan dibuat secara otomatis.</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--secondary" onclick="closeModal('createModal')">Batal</button>
                <button type="submit" class="btn btn--primary">Simpan Kategori</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title"><i class="bi bi-pencil text-warning"></i> Edit Kategori</h3>
            <button class="modal-close-btn" onclick="closeModal('editModal')"><i class="bi bi-x-lg"></i></button>
        </div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_name" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                    <input type="text" class="form-input" id="edit_name" name="name" required>
                    <span class="form-text-muted">Mengubah nama akan memperbarui slug URL.</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--secondary" onclick="closeModal('editModal')">Batal</button>
                <button type="submit" class="btn btn--primary">Update Kategori</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="deleteModal">
    <div class="modal-box" style="max-width: 400px; text-align: center;">
        <div class="modal-body" style="padding: 2.5rem 1.5rem 1.5rem;">
            <i class="bi bi-exclamation-circle text-danger" style="font-size: 4rem; color: #ef4444; margin-bottom: 1rem; display: block;"></i>
            <h3 style="font-size: 1.25rem; color: var(--gray-900); margin-bottom: 0.5rem;">Hapus Kategori?</h3>
            <p style="color: var(--gray-600); margin-bottom: 0;">
                Apakah Anda yakin ingin menghapus kategori <br><strong id="deleteCategoryName" style="color: var(--gray-900);"></strong>?
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
    // Fungsi Menutup Modal
    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('show');
    }

    // Modal Create
    function openCreateModal() {
        document.getElementById('create_name').value = '';
        document.getElementById('createModal').classList.add('show');
        // Auto focus input setelah modal muncul
        setTimeout(() => document.getElementById('create_name').focus(), 100);
    }

    // Modal Edit (Menangkap ID, Nama, dan Action Route dari tombol)
    function openEditModal(id, name, actionRoute) {
        // Set action form ke URL update yang tepat
        document.getElementById('editForm').action = actionRoute;
        // Isi input dengan nama lama
        document.getElementById('edit_name').value = name;
        // Tampilkan modal
        document.getElementById('editModal').classList.add('show');
    }

    // Modal Delete (Menangkap Nama dan Action Route dari tombol)
    function openDeleteModal(actionRoute, name) {
        // Set action form ke URL destroy yang tepat
        document.getElementById('deleteForm').action = actionRoute;
        // Ganti teks nama kategori di dalam modal peringatan
        document.getElementById('deleteCategoryName').textContent = '"' + name + '"';
        // Tampilkan modal
        document.getElementById('deleteModal').classList.add('show');
    }

    // Menutup modal jika user nge-klik area gelap di luar kotak modal
    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.classList.remove('show');
        }
    }
</script>
@endpush
@extends('admin.layouts.admin')

@section('title', 'Daftar Pengguna')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/users.css') }}">
@endpush

@section('content')
<div class="page-header">
    <h2 class="page-title"><i class="bi bi-people"></i> Daftar Pengguna</h2>
</div>

<div class="card" style="padding: 1.5rem 1.5rem 0 1.5rem; overflow: hidden;">
    
    <form action="{{ route('admin.users.index') }}" method="GET" class="filter-bar">
        <div class="d-flex gap-2">
            <input type="text" name="search" class="search-input" placeholder="Cari nama atau email..." value="{{ request('search') }}">
            <button type="submit" class="btn btn--primary">
                <i class="bi bi-search"></i> Cari
            </button>
            @if(request('search'))
                <a href="{{ route('admin.users.index') }}" class="btn btn--secondary">Reset</a>
            @endif
        </div>
        
        <div>
            <select name="sort" class="sort-select" onchange="this.form.submit()">
                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Terbaru Bergabung</option>
                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Terlama Bergabung</option>
                <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Nama A-Z</option>
                <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Nama Z-A</option>
            </select>
        </div>
    </form>

    <div class="table-wrapper" style="border-radius: 8px 8px 0 0; margin-bottom: 0; border-bottom: none;">
        @if($users->isEmpty())
            <div class="empty-state" style="padding: 3rem 1rem;">
                <i class="bi bi-person-x" style="font-size: 3rem; color: var(--gray-300); margin-bottom: 1rem; display: block;"></i>
                <p style="margin: 0; color: var(--gray-500);">Tidak ada pengguna yang ditemukan.</p>
            </div>
        @else
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Nama & Email</th>
                        <th>Peran</th>
                        <th class="text-center">Anak</th>
                        <th class="text-center">Screening</th>
                        <th class="text-center">Bergabung</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td class="td-wrap">
                            <strong style="color: var(--gray-900); display: block;">{{ $user->name }}</strong>
                            <span style="font-size: 0.85rem; color: var(--gray-500);">{{ $user->email }}</span>
                        </td>
                        <td class="td-nowrap">
                            <span class="badge badge--info">{{ ucfirst($user->role ?? 'Orang Tua') }}</span>
                        </td>
                        <td class="text-center td-nowrap">
                            <span style="font-weight: 600; color: var(--gray-700);">{{ $user->children_count }}</span>
                        </td>
                        <td class="text-center td-nowrap">
                            <span style="font-weight: 600; color: var(--accent-dark);">{{ $user->screenings_count }}</span>
                        </td>
                        <td class="text-center td-nowrap text-muted" style="font-size: 0.85rem;">
                            {{ $user->created_at->format('d M Y') }}
                        </td>
                        <td class="text-center td-nowrap">
                            <div class="d-flex gap-2" style="justify-content: center;">
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn--info btn--sm" title="Lihat Detail">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                                
                                @if(auth()->id() !== $user->id)
                                    <button type="button" class="btn btn--danger btn--sm" title="Hapus Akun"
                                            onclick="openDeleteModal('{{ route('admin.users.destroy', $user) }}', '{{ addslashes($user->name) }}')">
                                        <i class="bi bi-trash"></i>
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

<div style="background: var(--gray-50); padding: 1rem 1.5rem; border: 1px solid var(--gray-200); border-top: none; border-radius: 0 0 14px 14px; display: flex; justify-content: center;">
    {{ $users->appends(request()->query())->links() }}
</div>

<div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
        <div class="modal-body">
            <i class="bi bi-exclamation-triangle text-danger" style="font-size: 4rem; color: #ef4444; margin-bottom: 1rem; display: block;"></i>
            <h3 style="font-size: 1.25rem; color: var(--gray-900); margin-bottom: 0.5rem;">Hapus Pengguna?</h3>
            <p style="color: var(--gray-600); margin-bottom: 0;">
                Tindakan ini tidak dapat dibatalkan. Apakah Anda yakin ingin menghapus akun <br><strong id="deleteUserName" style="color: var(--gray-900);"></strong>?
            </p>
        </div>
        <form id="deleteForm" method="POST" class="modal-footer">
            @csrf
            @method('DELETE')
            <button type="button" class="btn btn--secondary" onclick="closeModal('deleteModal')">Batal</button>
            <button type="submit" class="btn btn--danger">Ya, Hapus Akun</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openDeleteModal(actionRoute, name) {
        document.getElementById('deleteForm').action = actionRoute;
        document.getElementById('deleteUserName').textContent = '"' + name + '"';
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
@extends('admin.layouts.admin')

@section('title', 'Detail User')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/users.css') }}">
@endpush

@section('content')
<div class="page-header">
    <h2 class="page-title"><i class="bi bi-person-circle"></i> Detail Pengguna</h2>
    <a href="{{ route('admin.users.index') }}" class="btn btn--secondary">
        <i class="bi bi-arrow-left"></i> Kembali ke Daftar
    </a>
</div>

<div class="content-grid">
    <div class="content-main">
        <div class="card">
            <div style="display: flex; gap: 1.5rem; align-items: center; margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--gray-200);">
                <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, var(--accent), var(--accent-dark)); color: var(--white); display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 700; flex-shrink: 0;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                
                <div>
                    <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--gray-900); margin: 0 0 0.3rem 0;">{{ $user->name }}</h1>
                    <div class="d-flex gap-2 align-center">
                        <span class="badge badge--info">{{ ucfirst($user->role ?? 'Orang Tua') }}</span>
                        <span style="color: var(--gray-500); font-size: 0.9rem;">
                            <i class="bi bi-envelope"></i> {{ $user->email }}
                        </span>
                    </div>
                </div>
            </div>

            <h4 style="font-size: 1.1rem; color: var(--gray-900); margin-bottom: 1rem;">Informasi Personal</h4>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; background: var(--gray-50); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--gray-200);">
                <div>
                    <span style="display: block; font-size: 0.8rem; color: var(--gray-500); text-transform: uppercase; font-weight: 600; margin-bottom: 0.2rem;">Nomor Telepon</span>
                    <span style="color: var(--gray-900); font-weight: 500;">{{ $user->phone ?? 'Tidak ada data' }}</span>
                </div>
                <div>
                    <span style="display: block; font-size: 0.8rem; color: var(--gray-500); text-transform: uppercase; font-weight: 600; margin-bottom: 0.2rem;">Bergabung Sejak</span>
                    <span style="color: var(--gray-900); font-weight: 500;">{{ $user->created_at->format('d M Y, H:i') }}</span>
                </div>
                <div>
                    <span style="display: block; font-size: 0.8rem; color: var(--gray-500); text-transform: uppercase; font-weight: 600; margin-bottom: 0.2rem;">Status Email</span>
                    @if($user->email_verified_at)
                        <span style="color: #059669; font-weight: 600;"><i class="bi bi-check-circle"></i> Terverifikasi</span>
                    @else
                        <span style="color: #d97706; font-weight: 600;"><i class="bi bi-x-circle"></i> Belum</span>
                    @endif
                </div>
            </div>

            <h4 style="font-size: 1.1rem; color: var(--gray-900); margin: 2rem 0 1rem;">Profil Anak Terdaftar</h4>
            @if($children->isEmpty())
                <div style="background: var(--gray-50); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--gray-200); text-align: center; color: var(--gray-500);">
                    Belum ada profil anak yang didaftarkan oleh pengguna ini.
                </div>
            @else
                <div class="table-wrapper" style="border: 1px solid var(--gray-200); border-radius: 12px;">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Nama Anak</th>
                                <th>Usia / Tanggal Lahir</th>
                                <th>Gender</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($children as $child)
                            <tr>
                                <td class="td-nowrap"><strong style="color: var(--gray-900);">{{ $child->name }}</strong></td>
                                <td class="td-nowrap">{{ $child->dob ? \Carbon\Carbon::parse($child->dob)->age . ' Tahun' : '-' }}</td>
                                <td class="td-nowrap">{{ ucfirst($child->gender ?? '-') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="content-sidebar">
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card__header">
                <i class="bi bi-activity"></i> Aktivitas Sistem
            </div>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--gray-600);">Total Profil Anak</span>
                    <span class="badge badge--secondary" style="font-size: 1rem;">{{ $user->children_count ?? 0 }} Data</span>
                </div>
                <hr style="border: none; border-top: 1px dashed var(--gray-200); margin: 0;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--gray-600);">Riwayat Screening</span>
                    <span class="badge badge--info" style="font-size: 1rem;">{{ $user->screenings_count ?? 0 }} Kali</span>
                </div>
            </div>
        </div>

        <div class="card" style="border-color: #fecaca; background: #fff5f5;">
            <div class="card__header" style="color: #ef4444; border-bottom-color: #fecaca;">
                <i class="bi bi-exclamation-triangle"></i> Pengaturan Akun
            </div>
            <p style="font-size: 0.85rem; color: #991b1b; margin-bottom: 1rem;">
                Menghapus akun akan menghilangkan seluruh data anak dan riwayat screening yang tertaut.
            </p>
            
            @if(auth()->id() !== $user->id)
                <button type="button" class="btn btn--danger btn--full" onclick="openDeleteModal('{{ route('admin.users.destroy', $user) }}', '{{ addslashes($user->name) }}')">
                    <i class="bi bi-trash"></i> Hapus Akun Permanen
                </button>
            @else
                <button type="button" class="btn btn--danger btn--full" disabled title="Anda tidak bisa menghapus akun Anda sendiri">
                    <i class="bi bi-lock"></i> Akun Saat Ini (Protected)
                </button>
            @endif
        </div>
    </div>
</div>

<div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
        <div class="modal-body">
            <i class="bi bi-exclamation-triangle text-danger" style="font-size: 4rem; color: #ef4444; margin-bottom: 1rem; display: block;"></i>
            <h3 style="font-size: 1.25rem; color: var(--gray-900); margin-bottom: 0.5rem;">Hapus Pengguna?</h3>
            <p style="color: var(--gray-600); margin-bottom: 0;">
                Tindakan ini permanen. Seluruh data yang terkait dengan <br><strong id="deleteUserName" style="color: var(--gray-900);"></strong> akan ikut terhapus. Lanjutkan?
            </p>
        </div>
        <form id="deleteForm" method="POST" class="modal-footer">
            @csrf
            @method('DELETE')
            <button type="button" class="btn btn--secondary" onclick="closeModal('deleteModal')">Batal</button>
            <button type="submit" class="btn btn--danger">Ya, Hapus Permanen</button>
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
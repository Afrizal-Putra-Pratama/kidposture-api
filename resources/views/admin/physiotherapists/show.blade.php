@extends('admin.layouts.admin')

@section('title', 'Detail Fisioterapis')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/physio-show.css') }}">
@endpush

@section('content')
<div class="page-header">
    <h2 class="page-title"><i class="bi bi-person-vcard"></i> Detail Fisioterapis</h2>
    <a href="{{ route('admin.physiotherapists.index') }}" class="btn btn--secondary">
        <i class="bi bi-arrow-left"></i> Kembali ke Daftar
    </a>
</div>

<div class="content-grid">
    <div class="content-main">
        <div class="card">
            
            <div class="physio-profile-header">
                <div class="physio-photo-frame">
                    @if ($physiotherapist->photo_url)
                        <img src="{{ $physiotherapist->photo_url }}" alt="Foto {{ $physiotherapist->name }}">
                    @else
                        <i class="bi bi-person physio-photo-placeholder"></i>
                    @endif
                </div>
                
                <div class="physio-header-info">
                    <h3 class="physio-name-title">{{ $physiotherapist->name }}</h3>
                    <div class="d-flex gap-2 mb-2">
                        <span class="badge badge--secondary">
                            <i class="bi bi-hospital"></i> {{ $physiotherapist->clinic_name ?? 'Tidak ada klinik' }}
                        </span>
                        <span class="badge badge--info">
                            <i class="bi bi-geo-alt"></i> {{ $physiotherapist->city ?? 'Kota tidak diketahui' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="physio-info-grid">
                <div class="info-box">
                    <span class="info-label">Spesialisasi</span>
                    <span class="info-value">{{ $physiotherapist->specialty ?? '-' }}</span>
                </div>
                <div class="info-box">
                    <span class="info-label">Pengalaman</span>
                    <span class="info-value">{{ $physiotherapist->experience_years ? $physiotherapist->experience_years . ' Tahun' : '-' }}</span>
                </div>
                <div class="info-box">
                    <span class="info-label">Nomor Telepon</span>
                    <span class="info-value">{{ $physiotherapist->phone ?? '-' }}</span>
                </div>
                <div class="info-box">
                    <span class="info-label">Email Akun</span>
                    <span class="info-value">{{ $physiotherapist->email ?? ($physiotherapist->user->email ?? '-') }}</span>
                </div>
            </div>

            <div style="margin-top: 2rem;">
                <h4 style="font-size: 1.1rem; color: var(--gray-900); margin-bottom: 0.75rem;">Biografi</h4>
                <div style="color: var(--gray-700); line-height: 1.6; font-size: 0.95rem; background: var(--gray-50); padding: 1.25rem; border-radius: 12px; border: 1px solid var(--gray-200);">
                    @if ($physiotherapist->bio_short || $physiotherapist->bio)
                        {{ $physiotherapist->bio_short ?? $physiotherapist->bio }}
                    @else
                        <span style="color: var(--gray-400); font-style: italic;">Belum ada biografi yang ditulis.</span>
                    @endif
                </div>
            </div>

            @if ($physiotherapist->certificate_url)
                <div class="certificate-box">
                    <div>
                        <strong style="display: block; color: var(--accent-dark); font-size: 1.1rem;">Sertifikat Keahlian</strong>
                        <span style="color: var(--gray-600); font-size: 0.85rem;">Dokumen ini diperlukan untuk verifikasi akun.</span>
                    </div>
                    <a href="{{ $physiotherapist->certificate_url }}" target="_blank" class="btn btn--primary">
                        <i class="bi bi-file-earmark-pdf"></i> Lihat Dokumen
                    </a>
                </div>
            @endif
            
        </div>
    </div>

    <div class="content-sidebar">
        <div class="card" style="position: sticky; top: 90px;">
            <div class="card__header">
                <i class="bi bi-shield-check"></i> Status Akun
            </div>
            
            <div class="action-box">
                <div class="status-row">
                    <span style="color: var(--gray-600); font-weight: 500;">Status Verifikasi</span>
                    @if ($physiotherapist->is_verified)
                        <span class="badge badge--success"><i class="bi bi-check-circle"></i> Verified</span>
                    @else
                        <span class="badge badge--warning" style="background: #fef3c7; color: #d97706;"><i class="bi bi-clock"></i> Menunggu</span>
                    @endif
                </div>
                
                <div class="status-row">
                    <span style="color: var(--gray-600); font-weight: 500;">Status Visibilitas</span>
                    @if ($physiotherapist->is_active)
                        <span class="badge badge--success">Aktif</span>
                    @else
                        <span class="badge badge--danger">Nonaktif</span>
                    @endif
                </div>

                <hr class="sidebar-divider" style="margin: 0.5rem 0;">

                <form method="POST" action="{{ route('admin.physiotherapists.approve', $physiotherapist) }}" style="width: 100%;">
                    @csrf
                    <button type="submit" class="btn btn--primary btn--full" style="padding: 0.8rem; {{ $physiotherapist->is_verified ? 'opacity: 0.5; cursor: not-allowed; background: var(--gray-400); border-color: transparent; box-shadow: none;' : '' }}" {{ $physiotherapist->is_verified ? 'disabled' : '' }}>
                        <i class="bi bi-patch-check"></i> 
                        {{ $physiotherapist->is_verified ? 'Sudah Diverifikasi' : 'Approve / Verifikasi' }}
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.physiotherapists.reject', $physiotherapist) }}" style="width: 100%;" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan akun fisioterapis ini?');">
                    @csrf
                    <button type="submit" class="btn btn--danger btn--full">
                        <i class="bi bi-ban"></i> Nonaktifkan Akun
                    </button>
                </form>
                
                <span class="form-text-muted text-center" style="margin-top: 0.5rem;">
                    *Akun nonaktif tidak akan muncul di aplikasi pasien.
                </span>
            </div>
        </div>
    </div>
</div>
@endsection
@extends('admin.layouts.admin')

@section('content')
<div class="container">
    <h1 class="mb-4">Detail Fisioterapis</h1>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <a href="{{ route('admin.physiotherapists.index') }}" class="btn btn-link mb-3">
        ← Kembali ke daftar
    </a>

    <div class="card mb-4">
        <div class="card-body">
            <h3>{{ $physiotherapist->name }}</h3>
            <p class="mb-1"><strong>Klinik:</strong> {{ $physiotherapist->clinic_name }}</p>
            <p class="mb-1"><strong>Kota:</strong> {{ $physiotherapist->city }}</p>
            <p class="mb-1"><strong>Spesialisasi:</strong> {{ $physiotherapist->specialty }}</p>
            <p class="mb-1"><strong>Pengalaman:</strong> {{ $physiotherapist->experience_years ?? '-' }} tahun</p>
            <p class="mb-1"><strong>Telepon:</strong> {{ $physiotherapist->phone }}</p>
            <p class="mb-1"><strong>Email:</strong> {{ $physiotherapist->email }}</p>

            <p class="mt-3">
                <strong>Status:</strong>
                @if ($physiotherapist->is_verified)
                    <span class="badge bg-success">Verified</span>
                @else
                    <span class="badge bg-secondary">Belum diverifikasi</span>
                @endif

                @if ($physiotherapist->is_active)
                    <span class="badge bg-success">Aktif</span>
                @else
                    <span class="badge bg-danger">Nonaktif</span>
                @endif
            </p>

            @if ($physiotherapist->bio_short)
                <hr>
                <p>{{ $physiotherapist->bio_short }}</p>
            @endif

            @if ($physiotherapist->photo_url)
                <hr>
                <p><strong>Foto Profil:</strong></p>
                <img src="{{ $physiotherapist->photo_url }}" alt="Foto" style="max-width: 200px;">
            @endif

            @if ($physiotherapist->certificate_url ?? false)
                <hr>
                <p><strong>Sertifikat:</strong></p>
                <a href="{{ $physiotherapist->certificate_url }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                    Lihat Sertifikat
                </a>
            @endif
        </div>
    </div>

    <div class="d-flex gap-2">
        <form method="POST" action="{{ route('admin.physiotherapists.approve', $physiotherapist) }}">
            @csrf
            <button type="submit" class="btn btn-success"
                {{ $physiotherapist->is_verified ? 'disabled' : '' }}>
                Approve / Verifikasi
            </button>
        </form>

        <form method="POST" action="{{ route('admin.physiotherapists.reject', $physiotherapist) }}">
            @csrf
            <button type="submit" class="btn btn-danger">
                Nonaktifkan
            </button>
        </form>
    </div>
</div>
@endsection

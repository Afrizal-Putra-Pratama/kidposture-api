@extends('admin.layouts.admin')

@section('title', 'Daftar Fisioterapis')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/physio-index.css') }}">
@endpush

@section('content')
<div class="page-header">
    <h2 class="page-title"><i class="bi bi-hospital"></i> Daftar Fisioterapis</h2>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    @if($physiotherapists->isEmpty())
        <div class="empty-state">
            <i class="bi bi-person-vcard" style="font-size: 3.5rem; color: var(--gray-300); margin-bottom: 1rem; display: block;"></i>
            <p style="margin-bottom: 0; color: var(--gray-500); font-size: 1.05rem;">Belum ada data fisioterapis yang terdaftar.</p>
        </div>
    @else
        <div class="table-wrapper" style="border: none; border-radius: 0;">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Nama Fisioterapis</th>
                        <th>Klinik</th>
                        <th>Kota</th>
                        <th>Spesialisasi</th>
                        <th class="text-center">Verifikasi</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($physiotherapists as $physio)
                    <tr>
                        <td class="td-wrap">
                            <span class="physio-name">{{ $physio->name }}</span>
                        </td>
                        
                        <td class="td-nowrap">
                            {{ $physio->clinic_name }}
                        </td>
                        
                        <td class="td-nowrap">
                            <i class="bi bi-geo-alt" style="color: var(--gray-400); margin-right: 0.3rem;"></i> 
                            {{ $physio->city }}
                        </td>
                        
                        <td class="td-nowrap">
                            <span class="badge badge--secondary">
                                {{ $physio->specialty ?? 'Umum' }}
                            </span>
                        </td>
                        
                        <td class="text-center td-nowrap">
                            @if ($physio->is_verified)
                                <span class="badge badge--success"><i class="bi bi-check-circle"></i> Verified</span>
                            @else
                                <span class="badge badge--warning" style="background: #fef3c7; color: #d97706;"><i class="bi bi-clock"></i> Pending</span>
                            @endif
                        </td>
                        
                        <td class="text-center td-nowrap">
                            @if ($physio->is_active)
                                <span class="badge badge--success">Aktif</span>
                            @else
                                <span class="badge badge--danger">Nonaktif</span>
                            @endif
                        </td>
                        
                        <td class="text-center td-nowrap">
                            <a href="{{ route('admin.physiotherapists.show', $physio) }}" class="btn btn--info btn--sm" title="Lihat Detail">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pagination-container">
            {{ $physiotherapists->links() }}
        </div>
    @endif
</div>
@endsection
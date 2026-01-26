@extends('admin.layouts.admin')

@section('content')
<div class="container">
    <h1 class="mb-4">Daftar Fisioterapis</h1>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Klinik</th>
                <th>Kota</th>
                <th>Spesialisasi</th>
                <th>Verified</th>
                <th>Aktif</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($physiotherapists as $physio)
                <tr>
                    <td>{{ $physio->name }}</td>
                    <td>{{ $physio->clinic_name }}</td>
                    <td>{{ $physio->city }}</td>
                    <td>{{ $physio->specialty }}</td>
                    <td>
                        @if ($physio->is_verified)
                            <span class="badge bg-success">Verified</span>
                        @else
                            <span class="badge bg-secondary">Pending</span>
                        @endif
                    </td>
                    <td>
                        @if ($physio->is_active)
                            <span class="badge bg-success">Aktif</span>
                        @else
                            <span class="badge bg-danger">Nonaktif</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.physiotherapists.show', $physio) }}" class="btn btn-sm btn-primary">
                            Detail
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Belum ada data fisioterapis.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $physiotherapists->links() }}
</div>
@endsection

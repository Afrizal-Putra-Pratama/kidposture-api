<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Menampilkan daftar semua pengguna (HANYA ROLE PARENT/USER BIASA).
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $sort = $request->get('sort', 'newest'); // default terbaru

        // Kunci query hanya untuk role 'parent'
        $query = User::where('role', User::ROLE_PARENT);

        // Jika ada pencarian, bungkus dalam function agar orWhere tidak menabrak filter role
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Pengurutan
        if ($sort === 'oldest') {
            $query->oldest();
        } elseif ($sort === 'name_asc') {
            $query->orderBy('name', 'asc');
        } elseif ($sort === 'name_desc') {
            $query->orderBy('name', 'desc');
        } else {
            $query->latest(); // newest
        }

        // Menghitung relasi anak dan screening agar bisa tampil di tabel
        $users = $query->withCount(['children', 'screenings'])->paginate(15);

        return view('admin.users.index', compact('users', 'search', 'sort'));
    }

    /**
     * Menampilkan detail spesifik pengguna (Profil, Statistik, dll).
     */
    public function show(User $user)
    {
        // Proteksi: Cegah akses jika ada yang iseng mengubah URL ke ID Admin/Fisio
        if ($user->role !== User::ROLE_PARENT) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Akses ditolak. Pengguna ini bukan entitas klien/parent.');
        }

        // Load jumlah data relasi untuk dashboard detail user
        $user->loadCount(['children', 'screenings']);
        
        // MENGAMBIL DATA ANAK
        $children = $user->children()->latest()->get(); 
        
        return view('admin.users.show', compact('user', 'children'));
    }

    /**
     * Fitur Suspend/Nonaktifkan User 
     */
    public function destroy(User $user)
    {
        // Proteksi ganda
        if ($user->role !== User::ROLE_PARENT) {
            return redirect()->back()->with('error', 'Hanya akun klien (parent) yang bisa dihapus dari menu ini.');
        }

        // Hapus akun
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Akun pengguna berhasil dihapus/dinonaktifkan.');
    }
}
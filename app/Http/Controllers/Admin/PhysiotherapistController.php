<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Physiotherapist;
use Illuminate\Http\Request;

class PhysiotherapistController extends Controller
{
    /**
     * Tampilkan list semua fisioterapis.
     */
    public function index()
    {
        $physiotherapists = Physiotherapist::with('user')
            ->latest()
            ->paginate(20);

        return view('admin.physiotherapists.index', compact('physiotherapists'));
    }

    /**
     * Tampilkan detail satu fisioterapis.
     */
    public function show(Physiotherapist $physiotherapist)
    {
        $physiotherapist->load('user');

        return view('admin.physiotherapists.show', compact('physiotherapist'));
    }

    /**
     * Form edit data profil (opsional, untuk admin).
     */
    public function edit(Physiotherapist $physiotherapist)
    {
        return view('admin.physiotherapists.edit', compact('physiotherapist'));
    }

    /**
     * Update data profil dari admin.
     */
    public function update(Request $request, Physiotherapist $physiotherapist)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'clinic_name' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'specialty' => ['nullable', 'string', 'max:255'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'bio_short' => ['nullable', 'string'],
            'is_accepting_consultations' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // checkbox bisa tidak terisi
        $data['is_accepting_consultations'] = $request->has('is_accepting_consultations');
        $data['is_active'] = $request->has('is_active');

        $physiotherapist->update($data);

        return redirect()
            ->route('admin.physiotherapists.show', $physiotherapist)
            ->with('status', 'Data fisioterapis berhasil diperbarui.');
    }

    /**
     * Hapus fisioterapis (opsional).
     */
    public function destroy(Physiotherapist $physiotherapist)
    {
        $physiotherapist->delete();

        return redirect()
            ->route('admin.physiotherapists.index')
            ->with('status', 'Fisioterapis berhasil dihapus.');
    }

    /**
     * Approve verifikasi fisioterapis + aktifkan akun.
     */
    public function approve(Physiotherapist $physiotherapist)
    {
        $physiotherapist->is_verified = true;
        $physiotherapist->is_active = true;
        $physiotherapist->save();

        if ($physiotherapist->user) {
            $physiotherapist->user->is_active = true;
            $physiotherapist->user->save();
        }

        return back()->with('status', 'Fisioterapis berhasil diverifikasi & diaktifkan.');
    }

    /**
     * Reject / nonaktifkan fisioterapis.
     */
    public function reject(Physiotherapist $physiotherapist)
    {
        $physiotherapist->is_verified = false;
        $physiotherapist->is_active = false;
        $physiotherapist->save();

        if ($physiotherapist->user) {
            $physiotherapist->user->is_active = false;
            $physiotherapist->user->save();
        }

        return back()->with('status', 'Fisioterapis dinonaktifkan.');
    }
}

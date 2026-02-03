<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Physiotherapist;
use Illuminate\Http\Request;

class PhysiotherapistController extends Controller
{
    /**
     * Halaman list semua fisioterapis (pending + verified)
     */
    public function index()
    {
        $physiotherapists = Physiotherapist::orderBy('created_at', 'desc')->paginate(15);


        return view('admin.physiotherapists.index', compact('physiotherapists'));
    }

    /**
     * Detail 1 fisioterapis
     */
    public function show(Physiotherapist $physiotherapist)
    {
        return view('admin.physiotherapists.show', compact('physiotherapist'));
    }

    /**
     * Form edit fisioterapis (opsional, kalau admin perlu edit data fisio)
     */
    public function edit(Physiotherapist $physiotherapist)
    {
        return view('admin.physiotherapists.edit', compact('physiotherapist'));
    }

    /**
     * Update data fisioterapis
     */
    public function update(Request $request, Physiotherapist $physiotherapist)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'clinic_name' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'specialty' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'consultation_fee' => 'nullable|numeric|min:0',
            'is_accepting_consultations' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $physiotherapist->update($validated);

        return redirect()
            ->route('admin.physiotherapists.show', $physiotherapist)
            ->with('success', 'Data fisioterapis berhasil diperbarui.');
    }

    /**
     * Hapus fisioterapis (soft delete atau hard delete sesuai kebutuhan)
     */
    public function destroy(Physiotherapist $physiotherapist)
    {
        $physiotherapist->delete();

        return redirect()
            ->route('admin.physiotherapists.index')
            ->with('success', 'Fisioterapis berhasil dihapus.');
    }

    /**
     * Approve / verifikasi fisioterapis
     */
    public function approve(Physiotherapist $physiotherapist)
    {
        $physiotherapist->update([
            'is_verified' => true,
            'is_active' => true,
            'verified_at' => now(),
        ]);

        return redirect()
            ->back()
            ->with('success', 'Fisioterapis berhasil diverifikasi.');
    }

    /**
     * Reject / batalkan verifikasi fisioterapis
     */
    public function reject(Physiotherapist $physiotherapist)
    {
        $physiotherapist->update([
            'is_verified' => false,
            'is_active' => false,
            'verified_at' => null,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Verifikasi fisioterapis dibatalkan.');
    }
}

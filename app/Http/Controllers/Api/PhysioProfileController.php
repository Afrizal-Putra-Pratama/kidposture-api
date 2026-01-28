<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhysioProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $physio = $user->physiotherapist;

        if (!$physio) {
            return response()->json([
                'success' => false,
                'message' => 'Profil fisioterapis tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $physio,
        ]);
    }

    public function update(Request $request)
{
    $user = $request->user();
    $physio = $user->physiotherapist;

    if (!$physio) {
        return response()->json([
            'success' => false,
            'message' => 'Profil fisioterapis tidak ditemukan.',
        ], 404);
    }

    $data = $request->validate([
        'name'        => 'sometimes|string|max:255',
        'phone'       => 'sometimes|string|max:20',
        'clinic_name' => 'sometimes|string|max:255',
        'city'        => 'sometimes|string|max:100',
        'address'     => 'nullable|string',
        'latitude'    => 'nullable|numeric|between:-90,90',
        'longitude'   => 'nullable|numeric|between:-180,180',
        'specialty'   => 'sometimes|string|max:255',
        'bio'         => 'nullable|string',
        'practice_hours'   => 'nullable|array',
        'consultation_fee' => 'nullable|integer|min:0',
        // ✅ hilangkan boolean agar tidak error 422
        'is_accepting_consultations' => 'sometimes',
        'photo'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ]);

    // ✅ konversi manual "true"/"false"/1/0 → boolean
    if ($request->has('is_accepting_consultations')) {
        $data['is_accepting_consultations'] = filter_var(
            $request->input('is_accepting_consultations'),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    // Upload foto profil
    if ($request->hasFile('photo')) {
        if ($physio->photo) {
            Storage::disk('public')->delete($physio->photo);
        }
        $data['photo'] = $request->file('photo')
            ->store('physio_photos', 'public');
    }

    $physio->update($data);

    return response()->json([
        'success' => true,
        'message' => 'Profil berhasil diperbarui.',
        'data'    => $physio,
    ]);
}

}

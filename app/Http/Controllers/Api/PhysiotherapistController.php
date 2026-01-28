<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Physiotherapist;
use Illuminate\Http\Request;

class PhysiotherapistController extends Controller
{
    /**
     * List fisioterapis aktif & menerima konsultasi
     * Dipakai:
     * - Landing page (preview)
     * - Parent saat pilih fisio untuk rujukan
     */
    public function index(Request $request)
    {
        $query = Physiotherapist::query()
            // ✅ pakai is_verified & is_active, bukan status = 'active'
            ->where('is_verified', true)
            ->where('is_active', true)
            ->where('is_accepting_consultations', true);

        if ($city = $request->query('city')) {
            $query->where('city', 'like', "%{$city}%");
        }

        if ($specialty = $request->query('specialty')) {
            $query->where('specialty', 'like', "%{$specialty}%");
        }

        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Sementara order by name
        $physios = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data'    => $physios,
        ]);
    }

    /**
     * Detail 1 fisioterapis
     */
    public function show($id)
    {
        $physio = Physiotherapist::findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $physio,
        ]);
    }
}

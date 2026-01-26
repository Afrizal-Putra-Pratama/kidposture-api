<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Physiotherapist;
use Illuminate\Http\Request;

class PhysiotherapistController extends Controller
{
    public function index(Request $request)
    {
        $query = Physiotherapist::query()
            ->where('is_accepting_consultations', true);

        if ($city = $request->query('city')) {
            $query->where('city', 'like', "%{$city}%");
        }

        if ($specialty = $request->query('specialty')) {
            $query->where('specialty', 'like', "%{$specialty}%");
        }

        $physios = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $physios,
        ]);
    }

    public function show($id)
    {
        $physio = Physiotherapist::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $physio,
        ]);
    }
}

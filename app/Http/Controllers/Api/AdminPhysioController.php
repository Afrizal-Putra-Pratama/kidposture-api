<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Physiotherapist;
use Illuminate\Http\Request;

class AdminPhysioController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'all'); // all, pending, active, rejected

        $query = Physiotherapist::with('user');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $physios = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $physios,
        ]);
    }

    public function approve($id)
    {
        $physio = Physiotherapist::findOrFail($id);

        $physio->update([
            'status' => 'active',
            'verified_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Fisioterapis berhasil disetujui.',
            'data' => $physio,
        ]);
    }

    public function reject($id)
    {
        $physio = Physiotherapist::findOrFail($id);

        $physio->update([
            'status' => 'rejected',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Fisioterapis ditolak.',
            'data' => $physio,
        ]);
    }
}

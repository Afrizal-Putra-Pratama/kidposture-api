<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Child;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChildController extends Controller
{
    public function index(Request $request)
    {
        try {
            $children = $request->user()->children()
                ->withCount('screenings')
                ->with('latestScreening')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $children->map(function ($child) {
                    return [
                        'id' => $child->id,
                        'name' => $child->name,
                        'gender' => $child->gender,
                        'birth_date' => $child->birth_date,
                        'weight' => $child->weight,
                        'height' => $child->height,
                        'age_years' => $child->age_years,
                        'screenings_count' => $child->screenings_count ?? 0,
                        'latest_screening' => $child->latestScreening ? [
                            'id' => $child->latestScreening->id,
                            'score' => $child->latestScreening->score ?? 0,
                            'category' => $child->latestScreening->category ?? 'N/A',
                            'summary' => $child->latestScreening->summary ?? '',
                            'created_at' => $child->latestScreening->created_at->toDateTimeString(),
                        ] : null,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        // ✅ Perketat validasi, samakan dengan form React
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date'],              // wajib, untuk age_years
            'gender' => ['required', Rule::in(['M', 'F'])],    // wajib, M/F
            'weight' => ['nullable', 'numeric', 'min:1', 'max:200'],
            'height' => ['nullable', 'numeric', 'min:30', 'max:220'],
        ]);

        $child = Child::create([
            'user_id' => $request->user()->id,
            ...$data,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Child created successfully',
            'data' => $child,
        ], 201);
    }
    public function update(Request $request, Child $child)
{
    // Pastikan child milik user yang login
    if ($child->user_id !== $request->user()->id) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized',
        ], 403);
    }

    $data = $request->validate([
        'name' => ['sometimes', 'required', 'string', 'max:255'],
        'birth_date' => ['sometimes', 'required', 'date'],
        'gender' => ['sometimes', 'required', Rule::in(['M', 'F'])],
        'weight' => ['nullable', 'numeric', 'min:1', 'max:200'],
        'height' => ['nullable', 'numeric', 'min:30', 'max:220'],
    ]);

    $child->update($data);

    return response()->json([
        'success' => true,
        'message' => 'Child updated successfully',
        'data' => $child,
    ]);
}

public function destroy(Request $request, Child $child)
{
    if ($child->user_id !== $request->user()->id) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized',
        ], 403);
    }

    $child->delete();

    return response()->json([
        'success' => true,
        'message' => 'Child deleted successfully',
    ]);
}
}

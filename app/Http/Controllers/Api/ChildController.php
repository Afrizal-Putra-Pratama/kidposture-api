<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Child;
use Illuminate\Http\Request;

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
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:M,F',
            'weight' => 'nullable|numeric',
            'height' => 'nullable|numeric',
        ]);

        $child = Child::create([
            'user_id' => $request->user()->id,
            ...$data,
        ]);

        return response()->json(['success' => true, 'data' => $child], 201);
    }
}

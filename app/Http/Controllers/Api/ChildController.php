<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Child;
use Illuminate\Http\Request;

class ChildController extends Controller
{
    public function index(Request $request)
    {
        $children = $request->user()->children()->latest()->get();

        return response()->json($children);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'gender'     => 'nullable|in:M,F',
            'weight'     => 'nullable|numeric',
            'height'     => 'nullable|numeric',
        ]);

        $child = Child::create([
            'user_id'    => $request->user()->id,
            ...$data,
        ]);

        return response()->json($child, 201);
    }
}

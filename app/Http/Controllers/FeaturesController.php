<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Features;
use Illuminate\Http\Request;

class FeaturesController extends Controller
{
    //
    public function index()
    {
        // Shob features fetch korchi
        $features = Features::all();

        return response()->json([
            'status' => 'success',
            'data' => $features
        ], 200);
    }

    /**
     * Store function: Notun Feature title save korar jonno
     */
    public function store(Request $request)
    {
        // Request validation
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        try {
            // Database-e data save kora
            $feature = Features::create([
                'title' => $request->title,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Feature title added successfully!',
                'data' => $feature
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save feature!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

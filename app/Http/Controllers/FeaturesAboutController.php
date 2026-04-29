<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AboutFeature;
use Illuminate\Http\Request;

class FeaturesAboutController extends Controller
{


public function storeAboutFeatures(Request $request)
{
    $request->validate([
        'aboutFeatures' => 'required|array',
        'aboutFeatures.*.category' => 'required|string',
        'aboutFeatures.*.feature' => 'required|string',
    ]);

    try {
        foreach ($request->aboutFeatures as $item) {
            \App\Models\AboutFeature::create([
                // Database Column => React Key
                'category_title' => $item['category'],
                'feature_detail' => $item['feature'],
            ]);
        }

        return response()->json(['status' => 'success', 'message' => 'Saved!'], 200);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
}

public function index()
{
    try {
        // 1. Database theke data group kore niye asha
        $features = AboutFeature::all()
            ->groupBy('category_title')
            ->map(function ($items, $category) {
                return [
                    'category' => $category,
                    // Sudhu feature_detail gulo ke ekta simple array-te niye asha
                    'details' => $items->pluck('feature_detail')
                ];
            })
            ->values(); // Array index reset korar jonno

        return response()->json([
            'status' => 'success',
            'data' => $features
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
}
}

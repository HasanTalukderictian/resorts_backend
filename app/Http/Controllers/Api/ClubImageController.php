<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClubGallery;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class ClubImageController extends Controller
{
    /**
     * Get all gallery items (paginated)
     */
    public function index(): JsonResponse
    {
        try {
            $galleries = ClubGallery::latest()->paginate(12);

            return response()->json([
                'success' => true,
                'data' => $galleries
            ], 200);

        } catch (Exception $e) {
            Log::error('Gallery Index Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }

    /**
     * Store new gallery item
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // ✅ FIX: proper validation (NO validated() bug)
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            ]);

            // Upload image
            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('galleries', 'public');
            }

            $gallery = ClubGallery::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Gallery item created successfully',
                'data' => $gallery
            ], 201);

        } catch (Exception $e) {
            Log::error('Gallery Store Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show single gallery item
     */
    public function show($id): JsonResponse
    {
        try {
            $gallery = ClubGallery::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $gallery
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gallery item not found'
            ], 404);
        }
    }

    /**
     * Update gallery item
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $gallery = ClubGallery::findOrFail($id);

            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            ]);

            // update title if exists
            if ($request->has('title')) {
                $gallery->title = $request->title;
            }

            // update image if exists
            if ($request->hasFile('image')) {

                // delete old image
                if ($gallery->image && Storage::disk('public')->exists($gallery->image)) {
                    Storage::disk('public')->delete($gallery->image);
                }

                // upload new image
                $gallery->image = $request->file('image')->store('galleries', 'public');
            }

            $gallery->save();

            return response()->json([
                'success' => true,
                'message' => 'Gallery updated successfully',
                'data' => $gallery
            ], 200);

        } catch (Exception $e) {
            Log::error('Gallery Update Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete gallery item
     */
    public function destroy($id): JsonResponse
    {
        try {
            $gallery = ClubGallery::findOrFail($id);

            // delete image
            if ($gallery->image && Storage::disk('public')->exists($gallery->image)) {
                Storage::disk('public')->delete($gallery->image);
            }

            $gallery->delete();

            return response()->json([
                'success' => true,
                'message' => 'Gallery deleted successfully'
            ], 200);

        } catch (Exception $e) {
            Log::error('Gallery Delete Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Delete failed'
            ], 500);
        }
    }
}

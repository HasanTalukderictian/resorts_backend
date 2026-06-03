<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class AchievementController extends Controller
{
    /**
     * Display all achievements
     */
    public function index()
    {
        try {

            $achievements = Achievement::latest()->get();

            return response()->json([
                'status' => true,
                'message' => 'Achievements retrieved successfully.',
                'data' => $achievements
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve achievements.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store achievement
     */
    public function store(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $imagePath = null;

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')
                    ->store('achievements', 'public');
            }

            $achievement = Achievement::create([
                'name' => $request->name,
                'image' => $imagePath
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Achievement created successfully.',
                'data' => $achievement
            ], 201);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Failed to create achievement.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show single achievement
     */
    public function show($id)
    {
        try {

            $achievement = Achievement::find($id);

            if (!$achievement) {
                return response()->json([
                    'status' => false,
                    'message' => 'Achievement not found.'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => $achievement
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve achievement.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update achievement
     */
    public function update(Request $request, $id)
    {
        try {

            $achievement = Achievement::find($id);

            if (!$achievement) {
                return response()->json([
                    'status' => false,
                    'message' => 'Achievement not found.'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            if ($request->hasFile('image')) {

                if ($achievement->image &&
                    Storage::disk('public')->exists($achievement->image)) {

                    Storage::disk('public')->delete($achievement->image);
                }

                $achievement->image = $request->file('image')
                    ->store('achievements', 'public');
            }

            $achievement->name = $request->name;
            $achievement->save();

            return response()->json([
                'status' => true,
                'message' => 'Achievement updated successfully.',
                'data' => $achievement
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Failed to update achievement.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete achievement
     */
    public function destroy($id)
    {
        try {

            $achievement = Achievement::find($id);

            if (!$achievement) {
                return response()->json([
                    'status' => false,
                    'message' => 'Achievement not found.'
                ], 404);
            }

            if ($achievement->image &&
                Storage::disk('public')->exists($achievement->image)) {

                Storage::disk('public')->delete($achievement->image);
            }

            $achievement->delete();

            return response()->json([
                'status' => true,
                'message' => 'Achievement deleted successfully.'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Failed to delete achievement.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

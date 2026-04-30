<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class VideoController extends Controller
{
    /**
     * 📌 Get All Videos
     */
    public function index(): JsonResponse
    {
        try {
            $videos = Video::latest()->get();

            return response()->json([
                'success' => true,
                'message' => 'Videos fetched successfully',
                'data'    => $videos
            ], 200);

        } catch (\Exception $e) {
            Log::error('Video index error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch videos'
            ], 500);
        }
    }

    /**
     * 📌 Store Video (Modal Submit)
     */
    public function store(Request $request): JsonResponse
    {
        // 🔒 Validation
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'video_url'   => 'required|url|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            // 🔥 Optional: Only One Record Allowed
            // Uncomment if needed

            if (Video::exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only one video allowed'
                ], 409);
            }


            $video = Video::create([
                'title'       => trim($request->title),
                'description' => trim($request->description),
                'video_url'   => trim($request->video_url),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Video created successfully',
                'data'    => $video
            ], 201);

        } catch (QueryException $e) {

            Log::error('Video store DB error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Database error occurred'
            ], 500);

        } catch (\Exception $e) {

            Log::error('Video store error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create video'
            ], 500);
        }
    }

    /**
     * 📌 Show Single Video
     */
    public function show($id): JsonResponse
    {
        try {
            $video = Video::find($id);

            if (!$video) {
                return response()->json([
                    'success' => false,
                    'message' => 'Video not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data'    => $video
            ], 200);

        } catch (\Exception $e) {
            Log::error('Video show error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch video'
            ], 500);
        }
    }

    /**
     * 📌 Update Video
     */
    public function update(Request $request, $id): JsonResponse
    {
        // 🔒 Validation
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'video_url'   => 'required|url|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $video = Video::find($id);

            if (!$video) {
                return response()->json([
                    'success' => false,
                    'message' => 'Video not found'
                ], 404);
            }

            $video->update([
                'title'       => trim($request->title),
                'description' => trim($request->description),
                'video_url'   => trim($request->video_url),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Video updated successfully',
                'data'    => $video
            ], 200);

        } catch (QueryException $e) {

            Log::error('Video update DB error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Database error occurred'
            ], 500);

        } catch (\Exception $e) {

            Log::error('Video update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update video'
            ], 500);
        }
    }

    /**
     * 📌 Delete Video
     */
    public function destroy($id): JsonResponse
    {
        try {
            $video = Video::find($id);

            if (!$video) {
                return response()->json([
                    'success' => false,
                    'message' => 'Video not found'
                ], 404);
            }

            $video->delete();

            return response()->json([
                'success' => true,
                'message' => 'Video deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Video delete error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete video'
            ], 500);
        }
    }
}

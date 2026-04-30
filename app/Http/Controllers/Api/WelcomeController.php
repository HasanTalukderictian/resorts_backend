<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Welcome;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

class WelcomeController extends Controller
{
    /**
     * Get All Records
     */
    public function index(): JsonResponse
    {
        try {
            $data = Welcome::latest()->get()->map(function ($item) {
                if ($item->image) {
                    $item->image = asset('storage/' . $item->image);
                }
                return $item;
            });

            return response()->json([
                'success' => true,
                'data'    => $data
            ], 200);
        } catch (Exception $e) {
            Log::error("Welcome index error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Internal Server Error!'], 500);
        }
    }

    /**
     * Store New Record with Validation
     */
    // public function store(Request $request): JsonResponse
    // {
    //     // ✅ API er jonno manually validation check
    //     $validator = Validator::make($request->all(), [
    //         'title'       => 'required|string|max:255',
    //         'description' => 'required|string',
    //         'image'       => 'required|image|mimes:jpeg,png,jpg,webp|max:2048', // 2MB Max
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'errors'  => $validator->errors()
    //         ], 422);
    //     }

    //     try {
    //         $data = $request->only(['title', 'description']);

    //         // ✅ Image Upload Logic
    //         if ($request->hasFile('image')) {
    //             $file = $request->file('image');
    //             $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
    //             $path = $file->storeAs('welcomes', $fileName, 'public');
    //             $data['image'] = $path;
    //         }

    //         $welcome = Welcome::create($data);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Record created successfully!',
    //             'data'    => $welcome
    //         ], 201);

    //     } catch (Exception $e) {
    //         Log::error("Welcome store error: " . $e->getMessage());
    //         return response()->json(['success' => false, 'message' => 'Failed to save record.'], 500);
    //     }
    // }



public function store(Request $request): JsonResponse
{
    // ✅ Step 1: Check if data already exists
    if (Welcome::exists()) {
        return response()->json([
            'success' => false,
            'message' => 'Welcome data already exists! You cannot add more.'
        ], 409);
    }

    // ✅ Step 2: Validation
    $validator = Validator::make($request->all(), [
        'title'       => 'required|string|max:255',
        'description' => 'required|string',
        'image'       => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors'  => $validator->errors()
        ], 422);
    }

    try {
        $data = $request->only(['title', 'description']);

        // ✅ Image Upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('welcomes', $fileName, 'public');
            $data['image'] = $path;
        }

        $welcome = Welcome::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Record created successfully!',
            'data'    => $welcome
        ], 201);

    } catch (Exception $e) {
        Log::error("Welcome store error: " . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Failed to save record.'
        ], 500);
    }
}


    /**
     * Delete Record
     */
    public function destroy($id): JsonResponse
    {
        try {
            $welcome = Welcome::findOrFail($id);

            if ($welcome->image && Storage::disk('public')->exists($welcome->image)) {
                Storage::disk('public')->delete($welcome->image);
            }

            $welcome->delete();

            return response()->json([
                'success' => true,
                'message' => 'Record deleted successfully!'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Record not found!'], 404);
        } catch (Exception $e) {
            Log::error("Welcome delete error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error!'], 500);
        }
    }
}

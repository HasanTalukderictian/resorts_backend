<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClubInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class ClubinfoController extends Controller
{
    /**
     * Display all club information
     */
    public function index()
    {
        try {

            $data = ClubInfo::latest()->get();

            return response()->json([
                'success' => true,
                'message' => 'Club information fetched successfully.',
                'data' => $data
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch club information.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store new club information
     */
   public function store(Request $request)
{
    DB::beginTransaction();

    try {

        // Check if record already exists
        if (ClubInfo::exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Club information already exists. Please update the existing record.'
            ], 409);
        }

        $validator = Validator::make($request->all(), [
            'club_name' => 'required|string|max:255',
            'club_history' => 'required|string',
            'club_phone' => 'required|string|max:20',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $validator->errors()
            ], 422);
        }

        $imagePath = null;

        if ($request->hasFile('image')) {

            $image = $request->file('image');
            $fileName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

            $image->move(public_path('uploads/club_infos'), $fileName);

            $imagePath = 'uploads/club_infos/' . $fileName;
        }

        $club = ClubInfo::create([
            'club_name' => $request->club_name,
            'club_history' => $request->club_history,
            'club_phone' => $request->club_phone,
            'image' => $imagePath,
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Club information created successfully.',
            'data' => $club
        ], 201);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => 'Failed to create club information.',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Show single club information
     */
    public function show($id)
    {
        try {

            $club = ClubInfo::find($id);

            if (!$club) {
                return response()->json([
                    'success' => false,
                    'message' => 'Club information not found.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Club information fetched successfully.',
                'data' => $club
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch club information.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update club information
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            $club = ClubInfo::find($id);

            if (!$club) {
                return response()->json([
                    'success' => false,
                    'message' => 'Club information not found.'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'club_name' => 'required|string|max:255',
                'club_history' => 'required|string',
                'club_phone' => 'required|string|max:20',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $imagePath = $club->image;

            if ($request->hasFile('image')) {

                if ($club->image && File::exists(public_path($club->image))) {
                    File::delete(public_path($club->image));
                }

                $image = $request->file('image');
                $fileName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

                $image->move(public_path('uploads/club_infos'), $fileName);

                $imagePath = 'uploads/club_infos/' . $fileName;
            }

            $club->update([
                'club_name' => $request->club_name,
                'club_history' => $request->club_history,
                'club_phone' => $request->club_phone,
                'image' => $imagePath,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Club information updated successfully.',
                'data' => $club->fresh()
            ], 200);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update club information.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete club information
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $club = ClubInfo::find($id);

            if (!$club) {
                return response()->json([
                    'success' => false,
                    'message' => 'Club information not found.'
                ], 404);
            }

            if ($club->image && File::exists(public_path($club->image))) {
                File::delete(public_path($club->image));
            }

            $club->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Club information deleted successfully.'
            ], 200);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete club information.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TeamMemberController extends Controller
{
    /**
     * Get All Members
     */
    public function index()
    {
        try {

            $members = TeamMember::latest()->get();

            return response()->json([
                'status' => true,
                'message' => 'Team members fetched successfully',
                'data' => $members
            ], 200);

        } catch (\Exception $e) {

            Log::error('Fetch Team Members Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Server Error'
            ], 500);
        }
    }

    /**
     * Store Member
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'name' => 'required|string|max:255',

            'designation' => 'required|string|max:255',

            'subtitle' => 'nullable|string|max:1000',

            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

        ]);

        if ($validator->fails()) {

            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {

            $imagePath = null;

            // Upload Image
            if ($request->hasFile('image')) {

                $file = $request->file('image');

                $path = $file->store('team-members', 'public');

                $imagePath = asset('storage/' . $path);
            }

            $member = TeamMember::create([

                'name' => $request->name,

                'designation' => $request->designation,

                'subtitle' => $request->subtitle,

                'image' => $imagePath,

                'status' => true
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Team member created successfully',
                'data' => $member
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Store Team Member Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Failed to create member'
            ], 500);
        }
    }

    /**
     * Show Single Member
     */
    public function show($id)
    {
        try {

            $member = TeamMember::find($id);

            if (!$member) {

                return response()->json([
                    'status' => false,
                    'message' => 'Member not found'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => $member
            ], 200);

        } catch (\Exception $e) {

            Log::error('Show Team Member Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Server Error'
            ], 500);
        }
    }

    /**
     * Update Member
     */
    public function update(Request $request, $id)
    {
        $member = TeamMember::find($id);

        if (!$member) {

            return response()->json([
                'status' => false,
                'message' => 'Member not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [

            'name' => 'required|string|max:255',

            'designation' => 'required|string|max:255',

            'subtitle' => 'nullable|string|max:1000',

            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

        ]);

        if ($validator->fails()) {

            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {

            $imagePath = $member->image;

            // Replace Image
            if ($request->hasFile('image')) {

                // Delete old image
                if ($member->image) {

                    $oldImage = str_replace(asset('storage/') . '/', '', $member->image);

                    Storage::disk('public')->delete($oldImage);
                }

                $file = $request->file('image');

                $path = $file->store('team-members', 'public');

                $imagePath = asset('storage/' . $path);
            }

            $member->update([

                'name' => $request->name,

                'designation' => $request->designation,

                'subtitle' => $request->subtitle,

                'image' => $imagePath
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Team member updated successfully',
                'data' => $member
            ], 200);

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Update Team Member Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Update failed'
            ], 500);
        }
    }

    /**
     * Delete Member
     */
    public function destroy($id)
    {
        $member = TeamMember::find($id);

        if (!$member) {

            return response()->json([
                'status' => false,
                'message' => 'Member not found'
            ], 404);
        }

        DB::beginTransaction();

        try {

            // Delete Image
            if ($member->image) {

                $oldImage = str_replace(asset('storage/') . '/', '', $member->image);

                Storage::disk('public')->delete($oldImage);
            }

            $member->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Member deleted successfully'
            ], 200);

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Delete Team Member Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Delete failed'
            ], 500);
        }
    }
}

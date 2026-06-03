<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvestRecord;
use App\Models\RecordMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RecordController extends Controller
{
    /**
     * Get all record members
     */
    public function index()
    {
        try {

            $data = RecordMember::latest()->get();

            return response()->json([
                'status' => true,
                'message' => 'Record members fetched successfully',
                'data' => $data
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch record members',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store new record member
     */
   public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'member'      => 'required|string|max:255',
        'revenue'     => 'required|numeric',
        'expericence' => 'required|string|max:255',
        'amenities'   => 'required|string'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => 'Validation error',
            'errors' => $validator->errors()
        ], 422);
    }

    try {

        // ✅ Check if record already exists
        $exists = RecordMember::exists();

        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'Record already exists. You cannot create more than one entry.'
            ], 409);
        }

        DB::beginTransaction();

        $record = RecordMember::create([
            'member'      => $request->member,
            'revenue'     => $request->revenue,
            'expericence' => $request->expericence,
            'amenities'   => $request->amenities
        ]);

        DB::commit();

        return response()->json([
            'status' => true,
            'message' => 'Record member created successfully',
            'data' => $record
        ], 201);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'status' => false,
            'message' => 'Failed to create record member',
            'error' => $e->getMessage()
        ], 500);
    }
}
    /**
     * Show single record member
     */
    public function show($id)
    {
        try {

            $record = RecordMember::find($id);

            if (!$record) {
                return response()->json([
                    'status' => false,
                    'message' => 'Record member not found'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Record member fetched successfully',
                'data' => $record
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch record member',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update record member
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'member'      => 'required|string|max:255',
            'revenue'     => 'required|numeric',
            'expericence' => 'required|string|max:255',
            'amenities'   => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {

            $record = RecordMember::find($id);

            if (!$record) {
                return response()->json([
                    'status' => false,
                    'message' => 'Record member not found'
                ], 404);
            }

            $record->update([
                'member'      => $request->member,
                'revenue'     => $request->revenue,
                'expericence' => $request->expericence,
                'amenities'   => $request->amenities
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Record member updated successfully',
                'data' => $record
            ], 200);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to update record member',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete record member
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $record = RecordMember::find($id);

            if (!$record) {
                return response()->json([
                    'status' => false,
                    'message' => 'Record member not found'
                ], 404);
            }

            $record->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Record member deleted successfully'
            ], 200);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to delete record member',
                'error' => $e->getMessage()
            ], 500);
        }
    }


   public function combinedData()
{
    try {

        $recordMembers = RecordMember::latest()->get();
        $investRecords = InvestRecord::latest()->get();

        return response()->json([
            'status' => true,
            'message' => 'Combined data fetched successfully',
            'data' => [
                'record_members' => $recordMembers,
                'invest_records' => $investRecords
            ]
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'status' => false,
            'message' => 'Failed to fetch combined data',
            'error' => $e->getMessage()
        ], 500);
    }
}



}

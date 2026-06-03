<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvestRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InvestReordController extends Controller
{
    /**
     * Get all records
     */
    public function index()
    {
        try {

            $data = InvestRecord::latest()->get();

            return response()->json([
                'status' => true,
                'message' => 'Invest records fetched successfully',
                'data' => $data
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch invest records',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store new record
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'desc'  => 'required|string'
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

            $record = InvestRecord::create([
                'title' => $request->title,
                'desc'  => $request->desc
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Invest record created successfully',
                'data' => $record
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to create invest record',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show single record
     */
    public function show($id)
    {
        try {

            $record = InvestRecord::find($id);

            if (!$record) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invest record not found'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Invest record found',
                'data' => $record
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch record',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update record
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'desc'  => 'required|string'
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

            $record = InvestRecord::find($id);

            if (!$record) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invest record not found'
                ], 404);
            }

            $record->update([
                'title' => $request->title,
                'desc'  => $request->desc
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Invest record updated successfully',
                'data' => $record
            ], 200);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to update record',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete record
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $record = InvestRecord::find($id);

            if (!$record) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invest record not found'
                ], 404);
            }

            $record->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Invest record deleted successfully'
            ], 200);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to delete record',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

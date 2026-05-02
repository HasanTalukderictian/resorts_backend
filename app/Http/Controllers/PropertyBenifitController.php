<?php

namespace App\Http\Controllers;

use App\Models\PropertyBenifit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PropertyBenifitController extends Controller
{
    /**
     * 📄 GET ALL (pagination + search optional)
     */
    public function index(Request $request)
    {
        try {
            $query = PropertyBenifit::query();

            // 🔍 Search (optional)
            if ($request->has('search')) {
                $search = $request->search;
                $query->where('title', 'like', "%$search%")
                      ->orWhere('desc', 'like', "%$search%");
            }

            $data = $query->latest()->paginate(10);

            return response()->json([
                'status' => true,
                'message' => 'Property benefits fetched successfully',
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📥 STORE
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'desc' => 'required|string'
        ]);

        DB::beginTransaction();

        try {
            $benefit = PropertyBenifit::create($validated);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Property benefit created successfully',
                'data' => $benefit
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔍 SHOW SINGLE
     */
    public function show($id)
    {
        $benefit = PropertyBenifit::find($id);

        if (!$benefit) {
            return response()->json([
                'status' => false,
                'message' => 'Property benefit not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $benefit
        ], 200);
    }

    /**
     * ✏️ UPDATE
     */
    public function update(Request $request, $id)
    {
        $benefit = PropertyBenifit::find($id);

        if (!$benefit) {
            return response()->json([
                'status' => false,
                'message' => 'Property benefit not found'
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'desc' => 'sometimes|required|string'
        ]);

        DB::beginTransaction();

        try {
            $benefit->update($validated);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Property benefit updated successfully',
                'data' => $benefit
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ❌ DELETE
     */
    public function destroy($id)
    {
        $benefit = PropertyBenifit::find($id);

        if (!$benefit) {
            return response()->json([
                'status' => false,
                'message' => 'Property benefit not found'
            ], 404);
        }

        DB::beginTransaction();

        try {
            $benefit->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Property benefit deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Delete failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

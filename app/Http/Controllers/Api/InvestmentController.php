<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvestmentPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class  InvestmentController extends Controller
{
    /**
     * Get all packages
     */
    public function index()
    {
        try {
            $packages = InvestmentPackage::latest()->get();
            return response()->json([
                'status'  => true,
                'message' => 'Packages retrieved successfully',
                'data'    => $packages
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error fetching packages: " . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Server Error'], 500);
        }
    }

    /**
     * Store a new package
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'        => 'required|string|max:255|unique:investment_packages,title',
            'price'        => 'nullable|string',
            'discount'     => 'nullable|string',
            'land'         => 'nullable|string',
            'building'     => 'nullable|string',
            'total_size'   => 'nullable|string',
            'description'  => 'nullable|string',
            'is_popular'   => 'boolean',
            'is_sold_out'  => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $package = InvestmentPackage::create($request->all());
            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Package created successfully',
                'data'    => $package
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error creating package: " . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to create package'], 500);
        }
    }

    /**
     * Get a single package
     */
    public function show($id)
    {
        $package = InvestmentPackage::find($id);

        if (!$package) {
            return response()->json(['status' => false, 'message' => 'Package not found'], 404);
        }

        return response()->json(['status' => true, 'data' => $package], 200);
    }

    /**
     * Update an existing package
     */
    public function update(Request $request, $id)
    {
        $package = InvestmentPackage::find($id);

        if (!$package) {
            return response()->json(['status' => false, 'message' => 'Package not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255|unique:investment_packages,title,' . $id,
            'is_popular'   => 'boolean',
            'is_sold_out'  => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $package->update($request->all());
            return response()->json([
                'status'  => true,
                'message' => 'Package updated successfully',
                'data'    => $package
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error updating package: " . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Update failed'], 500);
        }
    }

    /**
     * Delete a package
     */
    public function destroy($id)
    {
        $package = InvestmentPackage::find($id);

        if (!$package) {
            return response()->json(['status' => false, 'message' => 'Package not found'], 404);
        }

        try {
            $package->delete();
            return response()->json(['status' => true, 'message' => 'Package deleted successfully'], 200);
        } catch (\Exception $e) {
            Log::error("Error deleting package: " . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Delete failed'], 500);
        }
    }
}

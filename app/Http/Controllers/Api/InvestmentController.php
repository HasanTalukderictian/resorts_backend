<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvestmentPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class InvestmentController extends Controller
{
    /**
     * Get all packages
     */
    public function index()
    {
        try {
            $packages = InvestmentPackage::latest()->get();

            return response()->json([
                'status' => true,
                'message' => 'Packages retrieved successfully',
                'data' => $packages
            ], 200);

        } catch (\Exception $e) {

            Log::error("Error fetching packages: " . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Server Error'
            ], 500);
        }
    }

    /**
     * Store Package
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'title' => 'required|string|max:255|unique:investment_packages,title',
            'price' => 'nullable|string',
            'discount' => 'nullable|string',
            'land' => 'nullable|string',
            'building' => 'nullable|string',
            'total_size' => 'nullable|string',
            'description' => 'nullable|string',

            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',

            'is_popular' => 'boolean',
            'is_sold_out' => 'boolean',
        ]);

        if ($validator->fails()) {

            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {

            $imagePaths = [];

            // Multiple Image Upload
            if ($request->hasFile('images')) {

                foreach ($request->file('images') as $image) {

                    $path = $image->store('investment_packages', 'public');

                    $imagePaths[] = asset('storage/' . $path);
                }
            }

            $package = InvestmentPackage::create([
                'title' => $request->title,
                'price' => $request->price,
                'discount' => $request->discount,
                'land' => $request->land,
                'building' => $request->building,
                'total_size' => $request->total_size,
                'description' => $request->description,
                'images' => $imagePaths,
                'is_popular' => $request->is_popular ?? false,
                'is_sold_out' => $request->is_sold_out ?? false,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Package created successfully',
                'data' => $package
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error("Error creating package: " . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Failed to create package'
            ], 500);
        }
    }

    /**
     * Show Single Package
     */
    public function show($id)
    {
        $package = InvestmentPackage::find($id);

        if (!$package) {

            return response()->json([
                'status' => false,
                'message' => 'Package not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $package
        ], 200);
    }

    /**
     * Update Package
     */
    public function update(Request $request, $id)
    {
        $package = InvestmentPackage::find($id);

        if (!$package) {

            return response()->json([
                'status' => false,
                'message' => 'Package not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [

            'title' => 'sometimes|required|string|max:255|unique:investment_packages,title,' . $id,

            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',

            'is_popular' => 'boolean',
            'is_sold_out' => 'boolean',
        ]);

        if ($validator->fails()) {

            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {

            $imagePaths = $package->images ?? [];

            if ($request->hasFile('images')) {

                $imagePaths = [];

                foreach ($request->file('images') as $image) {

                    $path = $image->store('investment_packages', 'public');

                    $imagePaths[] = asset('storage/' . $path);
                }
            }

            $package->update([
                'title' => $request->title ?? $package->title,
                'price' => $request->price ?? $package->price,
                'discount' => $request->discount ?? $package->discount,
                'land' => $request->land ?? $package->land,
                'building' => $request->building ?? $package->building,
                'total_size' => $request->total_size ?? $package->total_size,
                'description' => $request->description ?? $package->description,
                'images' => $imagePaths,
                'is_popular' => $request->is_popular ?? $package->is_popular,
                'is_sold_out' => $request->is_sold_out ?? $package->is_sold_out,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Package updated successfully',
                'data' => $package
            ], 200);

        } catch (\Exception $e) {

            Log::error("Error updating package: " . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Update failed'
            ], 500);
        }
    }

    /**
     * Delete Package
     */
    public function destroy($id)
    {
        $package = InvestmentPackage::find($id);

        if (!$package) {

            return response()->json([
                'status' => false,
                'message' => 'Package not found'
            ], 404);
        }

        try {

            $package->delete();

            return response()->json([
                'status' => true,
                'message' => 'Package deleted successfully'
            ], 200);

        } catch (\Exception $e) {

            Log::error("Error deleting package: " . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Delete failed'
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackagePriceHistory;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    // =========================
    // GET ALL PACKAGES
    // =========================
    public function index()
    {
        $packages = Package::latest()->get();

        return response()->json([
            'success' => true,
            'data' => $packages
        ]);
    }

    // =========================
    // STORE PACKAGE
    // =========================
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'color' => 'nullable|string'
        ]);

        $price = $request->price;
        $discount = $request->discount ?? 0;

        $package = Package::create([
            'name' => $request->name,
            'old_price' => 0,
            'price' => $price,
            'discount' => $discount,
            'final_price' => $price - $discount,
            'color' => $request->color,
            'status' => 'active'
        ]);

        // Save Initial History
        PackagePriceHistory::create([
            'package_id' => $package->id,
            'old_price' => 0,
            'new_price' => $price,
            'discount' => $discount,
            'final_price' => $price - $discount,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Package created successfully',
            'data' => $package
        ]);
    }

    // =========================
    // UPDATE PACKAGE
    // =========================
    public function update(Request $request, $id)
    {
        $package = Package::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'color' => 'nullable|string'
        ]);

        $oldPrice = $package->price;

        $newPrice = $request->price;

        $discount = $request->discount ?? 0;

        // Update package
        $package->update([
            'name' => $request->name,
            'old_price' => $oldPrice,
            'price' => $newPrice,
            'discount' => $discount,
            'final_price' => $newPrice - $discount,
            'color' => $request->color,
        ]);

        // Save history
        PackagePriceHistory::create([
            'package_id' => $package->id,
            'old_price' => $oldPrice,
            'new_price' => $newPrice,
            'discount' => $discount,
            'final_price' => $newPrice - $discount,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Package updated successfully',
            'data' => $package
        ]);
    }

    // =========================
    // DELETE PACKAGE
    // =========================
    public function destroy($id)
    {
        $package = Package::findOrFail($id);

        $package->delete();

        return response()->json([
            'success' => true,
            'message' => 'Package deleted successfully'
        ]);
    }

    // =========================
    // PRICE HISTORY GRAPH API
    // =========================
    public function priceHistory($id)
    {
        $histories = PackagePriceHistory::where('package_id', $id)
            ->orderBy('changed_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $histories
        ]);
    }
}

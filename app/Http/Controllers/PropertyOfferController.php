<?php

namespace App\Http\Controllers;

use App\Models\PropertyOffer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PropertyOfferController extends Controller
{
    /**
     * 📄 GET ALL (with pagination)
     */
    public function index()
    {
        $offers = PropertyOffer::latest()->paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Property offers fetched successfully',
            'data' => $offers
        ], 200);
    }

    /**
     * 📥 STORE (CREATE)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'brand_name' => 'required|string|max:255',
            'whatsapp_number' => 'required|string|max:20',
            'description' => 'nullable|string',
            'features' => 'nullable|array',
            'features.*' => 'string|max:255',
            'slider_images' => 'nullable|array',
            'slider_images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5600'
        ]);

        DB::beginTransaction();

        try {
            $imagePaths = [];

            if ($request->hasFile('slider_images')) {
                foreach ($request->file('slider_images') as $image) {
                    $path = $image->store('property', 'public');
                    $imagePaths[] = $path;
                }
            }

            $data['slider_images'] = $imagePaths;

            $offer = PropertyOffer::create($data);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Property offer created successfully',
                'data' => $offer
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔍 SINGLE SHOW
     */
    public function show($id)
    {
        $offer = PropertyOffer::find($id);

        if (!$offer) {
            return response()->json([
                'status' => false,
                'message' => 'Property offer not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $offer
        ], 200);
    }

    /**
     * ✏️ UPDATE
     */
    public function update(Request $request, $id)
    {
        $offer = PropertyOffer::find($id);

        if (!$offer) {
            return response()->json([
                'status' => false,
                'message' => 'Property offer not found'
            ], 404);
        }

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'brand_name' => 'sometimes|required|string|max:255',
            'whatsapp_number' => 'sometimes|required|string|max:20',
            'description' => 'nullable|string',
            'features' => 'nullable|array',
            'features.*' => 'string|max:255',
            'slider_images' => 'nullable|array',
            'slider_images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5600'
        ]);

        DB::beginTransaction();

        try {
            // 🔥 If new images uploaded → delete old images
            if ($request->hasFile('slider_images')) {

                if (!empty($offer->slider_images)) {
                    foreach ($offer->slider_images as $oldImage) {
                        Storage::disk('public')->delete($oldImage);
                    }
                }

                $imagePaths = [];

                foreach ($request->file('slider_images') as $image) {
                    $path = $image->store('property', 'public');
                    $imagePaths[] = $path;
                }

                $data['slider_images'] = $imagePaths;
            }

            $offer->update($data);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Property offer updated successfully',
                'data' => $offer
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
        $offer = PropertyOffer::find($id);

        if (!$offer) {
            return response()->json([
                'status' => false,
                'message' => 'Property offer not found'
            ], 404);
        }

        DB::beginTransaction();

        try {
            // Delete images from storage
            if (!empty($offer->slider_images)) {
                foreach ($offer->slider_images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            $offer->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Property offer deleted successfully'
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

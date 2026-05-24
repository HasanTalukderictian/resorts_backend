<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PartnerController extends Controller
{
    /**
     * Display all partners
     */
    public function index(Request $request)
    {
        try {
            $query = Partner::query();

            // Search
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%")
                      ->orWhere('website', 'LIKE', "%{$search}%");
                });
            }

            // Filter by status
            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }

            // Ordering
            $query->ordered();

            // Pagination
            $partners = $query->paginate($request->get('per_page', 10));

            // Add image_url accessor
            $partners->getCollection()->transform(function ($partner) {
                $partner->image_url = $partner->image_url;
                return $partner;
            });

            return response()->json([
                'success' => true,
                'message' => 'Partners fetched successfully',
                'data' => $partners
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch partners',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created partner
     */
    public function store(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'website' => 'required|url|max:255',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'status' => 'nullable|in:active,inactive',
                'sort_order' => 'nullable|integer|min:0',
                'contact_email' => 'nullable|email|max:255',
                'contact_phone' => 'nullable|string|max:30'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->except('image');

            // Upload image
            if ($request->hasFile('image')) {

                $image = $request->file('image');

                $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();

                $imagePath = $image->storeAs(
                    'partners',
                    $imageName,
                    'public'
                );

                $data['image'] = $imagePath;
            }

            $partner = Partner::create($data);

            $partner->image_url = $partner->image_url;

            return response()->json([
                'success' => true,
                'message' => 'Partner created successfully',
                'data' => $partner
            ], 201);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to create partner',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display single partner
     */
    public function show($id)
    {
        try {

            $partner = Partner::find($id);

            if (!$partner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Partner not found'
                ], 404);
            }

            $partner->image_url = $partner->image_url;

            return response()->json([
                'success' => true,
                'message' => 'Partner fetched successfully',
                'data' => $partner
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch partner',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update partner
     */
    public function update(Request $request, $id)
    {
        try {

            $partner = Partner::find($id);

            if (!$partner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Partner not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                'website' => 'sometimes|required|url|max:255',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'status' => 'nullable|in:active,inactive',
                'sort_order' => 'nullable|integer|min:0',
                'contact_email' => 'nullable|email|max:255',
                'contact_phone' => 'nullable|string|max:30'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->except('image');

            // Update image
            if ($request->hasFile('image')) {

                // Delete old image
                if ($partner->image && Storage::disk('public')->exists($partner->image)) {
                    Storage::disk('public')->delete($partner->image);
                }

                $image = $request->file('image');

                $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();

                $imagePath = $image->storeAs(
                    'partners',
                    $imageName,
                    'public'
                );

                $data['image'] = $imagePath;
            }

            $partner->update($data);

            $partner->image_url = $partner->image_url;

            return response()->json([
                'success' => true,
                'message' => 'Partner updated successfully',
                'data' => $partner
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to update partner',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete partner
     */
    public function destroy($id)
    {
        try {

            $partner = Partner::find($id);

            if (!$partner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Partner not found'
                ], 404);
            }

            // Delete image
            if ($partner->image && Storage::disk('public')->exists($partner->image)) {
                Storage::disk('public')->delete($partner->image);
            }

            $partner->delete();

            return response()->json([
                'success' => true,
                'message' => 'Partner deleted successfully'
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete partner',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Active partners only
     */
    public function activePartners()
    {
        try {

            $partners = Partner::active()
                ->ordered()
                ->get();

            $partners->transform(function ($partner) {
                $partner->image_url = $partner->image_url;
                return $partner;
            });

            return response()->json([
                'success' => true,
                'message' => 'Active partners fetched successfully',
                'data' => $partners
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch active partners',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Increment click count
     */
    public function incrementClick($id)
    {
        try {

            $partner = Partner::find($id);

            if (!$partner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Partner not found'
                ], 404);
            }

            $partner->incrementClickCount();

            return response()->json([
                'success' => true,
                'message' => 'Click count updated successfully',
                'click_count' => $partner->fresh()->click_count
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to update click count',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

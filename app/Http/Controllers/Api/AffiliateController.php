<?php
// app/Http/Controllers/Api/AffiliateController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AffiliateController extends Controller
{
    /**
     * Display a listing of affiliates.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $affiliates = Affiliate::ordered()->get();

            return response()->json([
                'success' => true,
                'message' => 'Affiliates retrieved successfully',
                'data' => $affiliates->map(function ($affiliate) {
                    return [
                        'id' => $affiliate->id,
                        'title' => $affiliate->title,
                        'description' => $affiliate->description,
                        'website' => $affiliate->website,
                        'image' => $affiliate->image_url ?? asset('images/placeholder.png'),
                        'status' => $affiliate->status,
                        'created_at' => $affiliate->created_at->format('Y-m-d'),
                        'sort_order' => $affiliate->sort_order,
                        'click_count' => $affiliate->click_count
                    ];
                })
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve affiliates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created affiliate.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255|unique:affiliates,title',
            'description' => 'required|string|min:10',
            'website' => 'required|url|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'nullable|in:active,inactive',
            'sort_order' => 'nullable|integer',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->except('image');

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = Str::random(40) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('affiliates', $imageName, 'public');
                $data['image'] = $imagePath;
            }

            // Set default values
            $data['status'] = $request->status ?? 'active';
            $data['sort_order'] = $request->sort_order ?? Affiliate::max('sort_order') + 1;

            $affiliate = Affiliate::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Affiliate created successfully',
                'data' => [
                    'id' => $affiliate->id,
                    'title' => $affiliate->title,
                    'description' => $affiliate->description,
                    'website' => $affiliate->website,
                    'image' => $affiliate->image_url ?? asset('images/placeholder.png'),
                    'status' => $affiliate->status,
                    'created_at' => $affiliate->created_at->format('Y-m-d')
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create affiliate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified affiliate.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $affiliate = Affiliate::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Affiliate retrieved successfully',
                'data' => [
                    'id' => $affiliate->id,
                    'title' => $affiliate->title,
                    'description' => $affiliate->description,
                    'website' => $affiliate->website,
                    'image' => $affiliate->image_url ?? asset('images/placeholder.png'),
                    'status' => $affiliate->status,
                    'created_at' => $affiliate->created_at->format('Y-m-d'),
                    'sort_order' => $affiliate->sort_order,
                    'contact_email' => $affiliate->contact_email,
                    'contact_phone' => $affiliate->contact_phone,
                    'click_count' => $affiliate->click_count
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Affiliate not found'
            ], 404);
        }
    }

    /**
     * Update the specified affiliate.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255|unique:affiliates,title,' . $id,
            'description' => 'required|string|min:10',
            'website' => 'required|url|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'nullable|in:active,inactive',
            'sort_order' => 'nullable|integer',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $affiliate = Affiliate::findOrFail($id);
            $data = $request->except('image');

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($affiliate->image && Storage::disk('public')->exists($affiliate->image)) {
                    Storage::disk('public')->delete($affiliate->image);
                }

                $image = $request->file('image');
                $imageName = Str::random(40) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('affiliates', $imageName, 'public');
                $data['image'] = $imagePath;
            }

            $affiliate->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Affiliate updated successfully',
                'data' => [
                    'id' => $affiliate->id,
                    'title' => $affiliate->title,
                    'description' => $affiliate->description,
                    'website' => $affiliate->website,
                    'image' => $affiliate->image_url ?? asset('images/placeholder.png'),
                    'status' => $affiliate->status,
                    'updated_at' => $affiliate->updated_at->format('Y-m-d H:i:s')
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update affiliate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified affiliate.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $affiliate = Affiliate::findOrFail($id);

            // Delete image if exists
            if ($affiliate->image && Storage::disk('public')->exists($affiliate->image)) {
                Storage::disk('public')->delete($affiliate->image);
            }

            $affiliate->delete();

            return response()->json([
                'success' => true,
                'message' => 'Affiliate deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete affiliate'
            ], 500);
        }
    }

    /**
     * Update affiliate status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $affiliate = Affiliate::findOrFail($id);
            $affiliate->status = $request->status;
            $affiliate->save();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => [
                    'id' => $affiliate->id,
                    'status' => $affiliate->status
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status'
            ], 500);
        }
    }

    /**
     * Track affiliate click.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function trackClick($id)
    {
        try {
            $affiliate = Affiliate::findOrFail($id);
            $affiliate->incrementClickCount();

            return response()->json([
                'success' => true,
                'message' => 'Click tracked successfully',
                'data' => [
                    'id' => $affiliate->id,
                    'click_count' => $affiliate->click_count
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to track click'
            ], 500);
        }
    }

    /**
     * Bulk delete affiliates.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:affiliates,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $affiliates = Affiliate::whereIn('id', $request->ids)->get();

            // Delete images
            foreach ($affiliates as $affiliate) {
                if ($affiliate->image && Storage::disk('public')->exists($affiliate->image)) {
                    Storage::disk('public')->delete($affiliate->image);
                }
            }

            Affiliate::whereIn('id', $request->ids)->delete();

            return response()->json([
                'success' => true,
                'message' => count($request->ids) . ' affiliates deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete affiliates'
            ], 500);
        }
    }
}

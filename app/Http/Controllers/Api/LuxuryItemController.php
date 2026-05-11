<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LuxuryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class LuxuryItemController extends Controller
{
    /**
     * List
     */
   // LuxuryItemController.php তে index() মেথড আপডেট করুন:

public function index(Request $request)
{
    try {
        $search = $request->search;
        $query = LuxuryItem::query();

        if ($search) {
            $query->where('title', 'LIKE', "%{$search}%");
        }

        $items = $query->latest()->paginate(10);

        // ইমেজ পাথ থেকে 'storage/' বাদ দিন
        $items->getCollection()->transform(function ($item) {
            if ($item->image && strpos($item->image, 'storage/') === 0) {
                $item->image = str_replace('storage/', '', $item->image);
            }
            return $item;
        });

        return response()->json([
            'success' => true,
            'message' => 'Luxury items fetched successfully',
            'data' => $items
        ], 200);
    } catch (\Exception $e) {
        Log::error('Luxury Item List Error : ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Something went wrong'
        ], 500);
    }
}

    /**
     * Store
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'    => 'required|string|max:255|unique:luxury_items,title',
            // url এর বদলে এখন mimes validation হবে
            'image'    => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'features' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                // public disk এ আপলোড
                $file->move(public_path('uploads/luxury'), $filename);
                $imagePath = 'uploads/luxury/' . $filename;
            }

            // Remove Empty Feature
            $features = array_values(array_filter($request->features, fn($feature) => !empty(trim($feature))));

            $item = LuxuryItem::create([
                'title'    => trim($request->title),
                'image'    => $imagePath, // ফাইলের পাথ সেভ হবে
                'features' => $features,
                'status'   => 'active',
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Created successfully', 'data' => $item], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Luxury Item Store Error : ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to create item'], 500);
        }
    }
    /**
     * Show Single
     */
    public function show($id)
    {
        try {

            $item = LuxuryItem::find($id);

            if (!$item) {

                return response()->json([
                    'success' => false,
                    'message' => 'Luxury item not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $item
            ], 200);

        } catch (\Exception $e) {

            Log::error('Luxury Item Show Error : ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }

    /**
     * Update
     */
  public function update(Request $request, $id)
    {
        $item = LuxuryItem::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title'    => 'required|string|max:255|unique:luxury_items,title,' . $id,
            'image'    => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'status'   => 'required|in:active,inactive',
            'features' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $imagePath = $item->image; // পুরাতন ইমেজ পাথ

            if ($request->hasFile('image')) {
                // নতুন ইমেজ আসলে পুরাতনটা ডিলিট করে দিন (Optional but good practice)
                if ($imagePath && file_exists(public_path($imagePath))) {
                    unlink(public_path($imagePath));
                }

                $file = $request->file('image');
                $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/luxury'), $filename);
                $imagePath = 'uploads/luxury/' . $filename;
            }

            $features = array_values(array_filter($request->features, fn($feature) => !empty(trim($feature))));

            $item->update([
                'title'    => trim($request->title),
                'image'    => $imagePath,
                'features' => $features,
                'status'   => $request->status,
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Updated successfully', 'data' => $item], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Luxury Item Update Error : ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update'], 500);
        }
    }

    /**
     * Delete
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $item = LuxuryItem::find($id);

            if (!$item) {

                return response()->json([
                    'success' => false,
                    'message' => 'Luxury item not found'
                ], 404);
            }

            $item->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Luxury item deleted successfully'
            ], 200);

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Luxury Item Delete Error : ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete item'
            ], 500);
        }
    }

    /**
     * Status Change
     */
    public function changeStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [

            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {

            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {

            $item = LuxuryItem::find($id);

            if (!$item) {

                return response()->json([
                    'success' => false,
                    'message' => 'Luxury item not found'
                ], 404);
            }

            $item->update([
                'status' => $request->status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => $item
            ], 200);

        } catch (\Exception $e) {

            Log::error('Luxury Item Status Error : ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update status'
            ], 500);
        }
    }
}

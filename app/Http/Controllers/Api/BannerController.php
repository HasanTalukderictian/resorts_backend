<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class BannerController extends Controller
{
    /**
     * গেট একটিভ ব্যানার (ফ্রন্টএন্ডের জন্য)
     */
    public function getActiveBanner()
    {
        try {
            $banner = Banner::where('is_active', true)->latest()->first();

            if (!$banner) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No active banner found'
                ], 404);
            }

            // ইমেজের পাথগুলোকে ক্লিন করে ফুল URL-এ কনভার্ট করা
            $banner->images = collect($banner->images)->map(function ($img) {
                if (!$img) return null;
                // স্ল্যাশ ফরম্যাট ঠিক করা এবং ডাবল স্ল্যাশ এড়ানো
                $cleanPath = str_replace('\\', '/', $img);
                return asset('storage/' . ltrim($cleanPath, '/'));
            });

            return response()->json([
                'status' => 'success',
                'data' => $banner
            ], 200);

        } catch (Exception $e) {
            Log::error("Banner Fetch Error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong while fetching banner'
            ], 500);
        }
    }

    /**
     * নতুন ব্যানার ক্রিয়েট করা
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'images' => 'required|array|min:1',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120'
        ]);

        $uploadedImages = [];
        DB::beginTransaction();

        try {
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('banners', 'public');
                    if ($path) {
                        // ডাটাবেজে সেভ করার আগেই ব্যাকস্ল্যাশ ক্লিন করা
                        $uploadedImages[] = str_replace('\\', '/', $path);
                    }
                }
            }

            $banner = Banner::create([
                'title' => $request->title,
                'subtitle' => $request->subtitle,
                'slug' => $request->slug,
                'images' => $uploadedImages,
                'is_active' => true,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Banner created successfully!',
                'data' => $banner
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            foreach ($uploadedImages as $file) {
                Storage::disk('public')->delete($file);
            }
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * ব্যানার আপডেট করা
     */
    public function update(Request $request, $id)
    {
        try {
            $banner = Banner::findOrFail($id);

            $request->validate([
                'title' => 'sometimes|string|max:255',
                'subtitle' => 'sometimes|string|max:255',
                'slug' => 'sometimes|string|max:255',
                'images' => 'nullable|array',
                'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120'
            ]);

            $updatedImages = $banner->images;
            DB::beginTransaction();

            if ($request->hasFile('images')) {
                // পুরানো ইমেজ ডিলিট
                if (!empty($banner->images)) {
                    foreach ($banner->images as $oldImage) {
                        Storage::disk('public')->delete($oldImage);
                    }
                }

                $newUploadedImages = [];
                foreach ($request->file('images') as $image) {
                    $path = $image->store('banners', 'public');
                    if ($path) {
                        $newUploadedImages[] = str_replace('\\', '/', $path);
                    }
                }
                $updatedImages = $newUploadedImages;
            }

            $banner->update([
                'title' => $request->title ?? $banner->title,
                'subtitle' => $request->subtitle ?? $banner->subtitle,
                'slug' => $request->slug ?? $banner->slug,
                'images' => $updatedImages,
                'is_active' => $request->has('is_active') ? $request->is_active : $banner->is_active,
            ]);

            DB::commit();
            return response()->json(['status' => 'success', 'data' => $banner], 200);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * ব্যানার ডিলিট করা
     */
    public function destroy($id)
    {
        try {
            $banner = Banner::find($id);

            if (!$banner) {
                return response()->json(['status' => 'error', 'message' => 'Banner not found'], 404);
            }

            DB::beginTransaction();

            if (!empty($banner->images)) {
                foreach ($banner->images as $image) {
                    // ইমেজ পাথে ব্যাকস্ল্যাশ থাকলে তা ক্লিন করে চেক করা
                    $cleanPath = str_replace('\\', '/', $image);
                    if (Storage::disk('public')->exists($cleanPath)) {
                        Storage::disk('public')->delete($cleanPath);
                    }
                }
            }

            $banner->delete();
            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Deleted!']);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}

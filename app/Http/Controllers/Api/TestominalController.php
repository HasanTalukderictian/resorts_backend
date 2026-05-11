<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Testominal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Exception;

class TestominalController extends Controller
{
    /**
     * Fetch All Testimonials
     */
    public function index()
    {
        try {

            $testimonials = Testominal::latest()->get();

            return response()->json([
                'success' => true,
                'message' => 'Testimonials fetched successfully.',
                'data'    => $testimonials
            ], 200);

        } catch (Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch testimonials.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store New Testimonial
     */
    public function store(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'name'   => 'required|string|max:255',
                'image'  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'source' => 'nullable|string|max:100',
                'stars'  => 'required|integer|min:1|max:5',
                'text'   => 'required|string|max:5000',
            ]);

            // Validation Failed
            if ($validator->fails()) {

                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors'  => $validator->errors()
                ], 422);
            }

            $testimonial = new Testominal();

            $testimonial->name   = $request->name;
            $testimonial->source = $request->source;
            $testimonial->stars  = $request->stars;
            $testimonial->text   = $request->text;

            /**
             * Upload Image
             */
            if ($request->hasFile('image')) {

                $image = $request->file('image');

                $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();

                $imagePath = $image->storeAs(
                    'testimonials',
                    $imageName,
                    'public'
                );

                $testimonial->image = $imagePath;
            }

            $testimonial->save();

            return response()->json([
                'success' => true,
                'message' => 'Testimonial added successfully.',
                'data'    => $testimonial
            ], 201);

        } catch (Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while creating testimonial.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete Testimonial
     */
    public function destroy($id)
    {
        try {

            $testimonial = Testominal::find($id);

            // Not Found
            if (!$testimonial) {

                return response()->json([
                    'success' => false,
                    'message' => 'Testimonial not found.'
                ], 404);
            }

            /**
             * Delete Image
             */
            if ($testimonial->image && Storage::disk('public')->exists($testimonial->image)) {

                Storage::disk('public')->delete($testimonial->image);
            }

            $testimonial->delete();

            return response()->json([
                'success' => true,
                'message' => 'Testimonial deleted successfully.'
            ], 200);

        } catch (Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete testimonial.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class GalleryController extends Controller
{
    // List all gallery items
    public function index()
    {
        $items = Gallery::orderBy('created_at', 'desc')->get();
        return response()->json($items, 200);
    }

    // Store new image
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // Max 5MB
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('gallery', 'public');

            $gallery = Gallery::create([
                'title' => $request->title,
                'image_path' => $path
            ]);

            return response()->json(['message' => 'Image saved successfully!', 'data' => $gallery], 201);
        }
    }

    // Update existing item
    public function update(Request $request, $id)
    {
        $gallery = Gallery::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->hasFile('image')) {
            // Puron image delete kora
            Storage::disk('public')->delete($gallery->image_path);
            $gallery->image_path = $request->file('image')->store('gallery', 'public');
        }

        $gallery->title = $request->title;
        $gallery->save();

        return response()->json(['message' => 'Updated successfully!', 'data' => $gallery]);
    }

    // Delete item
    public function destroy($id)
    {
        $gallery = Gallery::findOrFail($id);
        Storage::disk('public')->delete($gallery->image_path);
        $gallery->delete();

        return response()->json(['message' => 'Deleted successfully!']);
    }
}

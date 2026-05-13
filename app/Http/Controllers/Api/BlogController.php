<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    // GET ALL
    public function index()
    {
        return response()->json([
            'status' => true,
            'data' => Blog::latest()->get()
        ]);
    }

    // GET SINGLE
      public function show($id)
    {
        $blog = Blog::find($id);

        if (!$blog) {
            return response()->json([
                'status' => false,
                'message' => 'Blog not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $blog
        ]);
    }

    // STORE
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'author' => 'required',
            'category' => 'required',
            'status' => 'required',
            'excerpt' => 'required',
            'introduction' => 'required',
            'image' => 'nullable|image|max:2048'
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('blogs', 'public');
        }

        $blog = Blog::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'author' => $request->author,
            'category' => $request->category,
            'status' => $request->status,
            'excerpt' => $request->excerpt,
            'image' => $imagePath,
            'introduction' => $request->introduction,
            'sections' => json_decode($request->sections, true) ?? [],
            'conclusion' => $request->conclusion,
            'views' => 0,
            'likes' => 0,
            'read_time' => '5 min read',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Blog Created',
            'data' => $blog
        ]);
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $blog = Blog::findOrFail($id);

        $request->validate([
            'title' => 'required',
            'author' => 'required',
            'category' => 'required',
            'status' => 'required',
            'excerpt' => 'required',
            'introduction' => 'required',
        ]);

        $imagePath = $blog->image;

        if ($request->hasFile('image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            $imagePath = $request->file('image')->store('blogs', 'public');
        }

        $blog->update([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'author' => $request->author,
            'category' => $request->category,
            'status' => $request->status,
            'excerpt' => $request->excerpt,
            'image' => $imagePath,
            'introduction' => $request->introduction,
            'sections' => json_decode($request->sections, true) ?? [],
            'conclusion' => $request->conclusion,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Blog Updated',
            'data' => $blog
        ]);
    }

    // DELETE
    public function destroy($id)
    {
        $blog = Blog::findOrFail($id);

        if ($blog->image) {
            Storage::disk('public')->delete($blog->image);
        }

        $blog->delete();

        return response()->json([
            'status' => true,
            'message' => 'Blog Deleted'
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Query;
use Illuminate\Http\Request;

class QueryController extends Controller
{
    // Get All Queries
    public function index()
    {
        $queries = Query::latest()->get();

        return response()->json([
            'status' => true,
            'data' => $queries
        ]);
    }

    // Store Query
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'message' => 'required|string',
        ]);

        $query = Query::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'message' => $request->message,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Query submitted successfully',
            'data' => $query
        ], 201);
    }

    // Single Query
    public function show($id)
    {
        $query = Query::findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => $query
        ]);
    }

    // Delete Query
    public function destroy($id)
    {
        $query = Query::findOrFail($id);
        $query->delete();

        return response()->json([
            'status' => true,
            'message' => 'Query deleted successfully'
        ]);
    }
}

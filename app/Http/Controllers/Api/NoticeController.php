<?php
// app/Http/Controllers/Api/NoticeController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class NoticeController extends Controller
{
    /**
     * Display a listing of the notices.
     */
    public function index(Request $request)
    {
        try {
            $query = Notice::query();

            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $query->where('text', 'like', '%' . $request->search . '%');
            }

            // Filter by status
            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $notices = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Notices retrieved successfully',
                'data' => $notices
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve notices',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created notice.
     */
    public function store(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'text' => 'required|string|min:10|max:1000',
                'status' => 'sometimes|in:Active,Inactive'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create notice
            $notice = Notice::create([
                'text' => $request->text,
                'status' => $request->status ?? 'Active',
                'created_by' => Auth::user()->name ?? 'Admin'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notice created successfully',
                'data' => $notice
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create notice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified notice.
     */
    public function show($id)
    {
        try {
            $notice = Notice::find($id);

            if (!$notice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notice not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notice retrieved successfully',
                'data' => $notice
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve notice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified notice.
     */
    public function update(Request $request, $id)
    {
        try {
            $notice = Notice::find($id);

            if (!$notice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notice not found'
                ], 404);
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'text' => 'sometimes|required|string|min:10|max:1000',
                'status' => 'sometimes|in:Active,Inactive'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update notice
            if ($request->has('text')) {
                $notice->text = $request->text;
            }
            if ($request->has('status')) {
                $notice->status = $request->status;
            }

            $notice->save();

            return response()->json([
                'success' => true,
                'message' => 'Notice updated successfully',
                'data' => $notice
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update notice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified notice.
     */
    public function destroy($id)
    {
        try {
            $notice = Notice::find($id);

            if (!$notice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notice not found'
                ], 404);
            }

            $notice->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notice deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete notice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete notices.
     */
    public function bulkDelete(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'exists:notices,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $deleted = Notice::whereIn('id', $request->ids)->delete();

            return response()->json([
                'success' => true,
                'message' => $deleted . ' notices deleted successfully',
                'data' => ['deleted_count' => $deleted]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete notices',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle notice status.
     */
    public function toggleStatus($id)
    {
        try {
            $notice = Notice::find($id);

            if (!$notice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notice not found'
                ], 404);
            }

            $notice->status = $notice->status === 'Active' ? 'Inactive' : 'Active';
            $notice->save();

            return response()->json([
                'success' => true,
                'message' => 'Notice status toggled successfully',
                'data' => $notice
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle notice status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get only active notices for frontend display.
     */
    public function getActiveNotices()
    {
        try {
            $notices = Notice::active()
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Active notices retrieved successfully',
                'data' => $notices
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve active notices',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

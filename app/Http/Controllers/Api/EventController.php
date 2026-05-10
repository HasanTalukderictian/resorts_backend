<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventController extends Controller
{
    /**
     * Get all events with pagination and filters
     */
    public function index(Request $request)
    {
        $query = Event::query();

        // Apply status filter
        if ($request->has('status') && in_array($request->status, ['active', 'inactive'])) {
            $query->where('status', $request->status);
        } else {
            $query->active();
        }

        // Apply upcoming/past filter
        if ($request->has('type')) {
            if ($request->type === 'upcoming') {
                $query->upcoming();
            } elseif ($request->type === 'past') {
                $query->past();
            }
        }

        // Search by title
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('main_title', 'like', '%' . $request->search . '%');
        }

        // Sort by date
        $sortBy = $request->get('sort_by', 'event_datetime');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 10);
        $events = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Events retrieved successfully',
            'data' => $events->items(),
            'pagination' => [
                'current_page' => $events->currentPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total(),
                'last_page' => $events->lastPage()
            ]
        ]);
    }

    /**
     * Get all events for frontend (no pagination for homepage)
     */
    public function getAllEvents()
    {
        $events = Event::active()
            ->orderBy('event_datetime', 'asc')
            ->get();

        // Format data to match frontend structure
        $formattedEvents = $events->map(function($event) {
            return $this->formatEventForFrontend($event);
        });

        return response()->json([
            'success' => true,
            'data' => $formattedEvents
        ]);
    }

    /**
     * Get single event by ID or slug
     */
    public function show($identifier)
    {
        $event = is_numeric($identifier)
            ? Event::find($identifier)
            : Event::where('slug', $identifier)->first();

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found'
            ], 404);
        }

        // Increment view count
        $event->incrementViewCount();

        return response()->json([
            'success' => true,
            'data' => $this->formatEventForFrontend($event)
        ]);
    }

    /**
     * Create new event
     */
    public function store(Request $request)
    {
        try {
            // Convert features to array if it's a string
            $features = $request->features;
            if (is_string($features)) {
                $features = json_decode($features, true);
            }

            // Handle array format from FormData (features[0], features[1], etc.)
            if ($request->has('features')) {
                $featuresArray = [];
                foreach ($request->all() as $key => $value) {
                    if (str_starts_with($key, 'features[')) {
                        $featuresArray[] = $value;
                    }
                }
                if (!empty($featuresArray)) {
                    $features = $featuresArray;
                }
            }

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'event_datetime' => 'required|date',
                'thumb_img' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'main_img' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'main_title' => 'required|string|max:255',
                'subtitle' => 'required|string|max:255',
                'posted_by' => 'required|string|max:100',
                'description' => 'required|string',
                'status' => 'nullable|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Handle image uploads
            $thumbImgPath = $request->file('thumb_img')->store('events/thumbnails', 'public');
            $mainImgPath = $request->file('main_img')->store('events/main', 'public');

            // Parse date
            $eventDate = new \DateTime($request->event_datetime);

            $event = Event::create([
                'title' => $request->title,
                'slug' => Str::slug($request->title . '-' . time()),
                'event_datetime' => $request->event_datetime,
                'thumb_img' => $thumbImgPath,
                'main_img' => $mainImgPath,
                'date_day' => $eventDate->format('d'),
                'date_month' => $eventDate->format('M'),
                'main_title' => $request->main_title,
                'subtitle' => $request->subtitle,
                'posted_by' => $request->posted_by,
                'features' => $features ?? [],
                'description' => $request->description,
                'status' => $request->status ?? 'active',
                'comments_count' => 0,
                'view_count' => 0
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Event created successfully',
                'data' => $this->formatEventForFrontend($event)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create event: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update event
     */
    public function update(Request $request, $id)
    {
        try {
            $event = Event::find($id);

            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event not found'
                ], 404);
            }

            // Convert features to array if it's a string
            $features = $request->features;
            if (is_string($features)) {
                $features = json_decode($features, true);
            }

            // Handle array format from FormData (features[0], features[1], etc.)
            if ($request->has('features')) {
                $featuresArray = [];
                foreach ($request->all() as $key => $value) {
                    if (str_starts_with($key, 'features[')) {
                        $featuresArray[] = $value;
                    }
                }
                if (!empty($featuresArray)) {
                    $features = $featuresArray;
                }
            }

            $validator = Validator::make($request->all(), [
                'title' => 'nullable|string|max:255',
                'event_datetime' => 'nullable|date',
                'thumb_img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'main_img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'main_title' => 'nullable|string|max:255',
                'subtitle' => 'nullable|string|max:255',
                'posted_by' => 'nullable|string|max:100',
                'description' => 'nullable|string',
                'status' => 'nullable|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update thumb image if provided
            if ($request->hasFile('thumb_img')) {
                // Delete old image
                if ($event->thumb_img && Storage::disk('public')->exists($event->thumb_img)) {
                    Storage::disk('public')->delete($event->thumb_img);
                }
                $thumbImgPath = $request->file('thumb_img')->store('events/thumbnails', 'public');
                $event->thumb_img = $thumbImgPath;
            }

            // Update main image if provided
            if ($request->hasFile('main_img')) {
                // Delete old image
                if ($event->main_img && Storage::disk('public')->exists($event->main_img)) {
                    Storage::disk('public')->delete($event->main_img);
                }
                $mainImgPath = $request->file('main_img')->store('events/main', 'public');
                $event->main_img = $mainImgPath;
            }

            // Update date fields if datetime changed
            if ($request->has('event_datetime') && $request->event_datetime) {
                $eventDate = new \DateTime($request->event_datetime);
                $event->date_day = $eventDate->format('d');
                $event->date_month = $eventDate->format('M');
                $event->event_datetime = $request->event_datetime;
            }

            // Update other fields
            if ($request->has('title')) {
                $event->title = $request->title;
                $event->slug = Str::slug($request->title . '-' . time());
            }
            if ($request->has('main_title')) {
                $event->main_title = $request->main_title;
            }
            if ($request->has('subtitle')) {
                $event->subtitle = $request->subtitle;
            }
            if ($request->has('posted_by')) {
                $event->posted_by = $request->posted_by;
            }
            if ($request->has('description')) {
                $event->description = $request->description;
            }
            if ($features !== null) {
                $event->features = $features;
            }
            if ($request->has('status')) {
                $event->status = $request->status;
            }

            $event->save();

            return response()->json([
                'success' => true,
                'message' => 'Event updated successfully',
                'data' => $this->formatEventForFrontend($event)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update event: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete event (soft delete)
     */
    public function destroy($id)
    {
        try {
            $event = Event::find($id);

            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event not found'
                ], 404);
            }

            // Delete images from storage
            if ($event->thumb_img && Storage::disk('public')->exists($event->thumb_img)) {
                Storage::disk('public')->delete($event->thumb_img);
            }
            if ($event->main_img && Storage::disk('public')->exists($event->main_img)) {
                Storage::disk('public')->delete($event->main_img);
            }

            $event->delete();

            return response()->json([
                'success' => true,
                'message' => 'Event deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete event: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore soft-deleted event
     */
    public function restore($id)
    {
        try {
            $event = Event::withTrashed()->find($id);

            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event not found'
                ], 404);
            }

            $event->restore();

            return response()->json([
                'success' => true,
                'message' => 'Event restored successfully',
                'data' => $this->formatEventForFrontend($event)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore event: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update event status (active/inactive)
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $event = Event::find($id);

            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event not found'
                ], 404);
            }

            $event->status = $request->status;
            $event->save();

            return response()->json([
                'success' => true,
                'message' => 'Event status updated successfully',
                'data' => $this->formatEventForFrontend($event)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format event data for frontend with full image URLs
     */
    private function formatEventForFrontend($event)
    {
        // Generate full URLs for images
        $thumbImgUrl = $event->thumb_img ? url('storage/' . $event->thumb_img) : null;
        $mainImgUrl = $event->main_img ? url('storage/' . $event->main_img) : null;

        // If images are already stored with storage/ prefix
        if ($event->thumb_img && !str_starts_with($event->thumb_img, 'storage/')) {
            $thumbImgUrl = url('storage/' . $event->thumb_img);
        }
        if ($event->main_img && !str_starts_with($event->main_img, 'storage/')) {
            $mainImgUrl = url('storage/' . $event->main_img);
        }

        return [
            'id' => $event->id,
            'title' => $event->title,
            'time' => $event->formatted_datetime,
            'thumbImg' => $thumbImgUrl,
            'mainImg' => $mainImgUrl,
            'dateBox' => [
                'day' => (int)$event->date_day,
                'month' => $event->date_month
            ],
            'mainTitle' => $event->main_title,
            'subtitle' => $event->subtitle,
            'postedBy' => $event->posted_by,
            'comments' => $event->comments_count,
            'features' => is_array($event->features) ? $event->features : json_decode($event->features, true) ?? [],
            'description' => $event->description,
            'slug' => $event->slug,
            'status' => $event->status,
            'view_count' => $event->view_count,
            'event_datetime' => $event->event_datetime,
            'created_at' => $event->created_at,
            'updated_at' => $event->updated_at
        ];
    }
}

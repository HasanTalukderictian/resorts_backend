<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvestmentBenefit;
use App\Models\InvestmentPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InvestmentBenefitController extends Controller
{



    public function index()
    {
        try {
            // লেটেস্ট ডেটা আগে দেখানোর জন্য (Pagination সহ)
            $benefits = InvestmentBenefit::latest()->paginate(10);

            return response()->json([
                'status' => true,
                'message' => 'Data fetched successfully!',
                'data' => $benefits
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch data!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

  public function store(Request $request)
{
    // ১. ভ্যালিডেশন
    $validator = Validator::make($request->all(), [
        'title' => 'required|string|max:255',
        'subtitle' => 'required|string|max:255',
        'benefits' => 'required|array|min:1',
        'benefits.*' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
    }

    try {
        // ২. চেক করা হচ্ছে ডেটা আগে থেকেই আছে কিনা
        $exists = InvestmentBenefit::exists(); // ডেটা থাকলে true দিবে

        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'Record already exists! You can only update the existing one.'
            ], 400); // Bad Request
        }

        // ৩. ডেটা সেভ (যদি আগে কোনো রেকর্ড না থাকে)
        $data = InvestmentBenefit::create([
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'benefits' => $request->benefits,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Investment Benefit saved successfully!',
            'data' => $data
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Something went wrong!',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // ১. আইডি চেক করা
        $item = InvestmentBenefit::find($id);

        if (!$item) {
            return response()->json([
                'status' => false,
                'message' => 'Record not found!'
            ], 404);
        }

        // ২. ভ্যালিডেশন
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'subtitle' => 'sometimes|required|string|max:255',
            'benefits' => 'sometimes|required|array|min:1',
            'benefits.*' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // ৩. আপডেট
            $item->update($request->only(['title', 'subtitle', 'benefits']));

            return response()->json([
                'status' => true,
                'message' => 'Investment Benefit updated successfully!',
                'data' => $item
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Update failed!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $item = InvestmentBenefit::find($id);

            if (!$item) {
                return response()->json([
                    'status' => false,
                    'message' => 'Record not found!'
                ], 404);
            }

            $item->delete();

            return response()->json([
                'status' => true,
                'message' => 'Investment Benefit deleted successfully!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Delete operation failed!',
                'error' => $e->getMessage()
            ], 500);
        }
    }


}

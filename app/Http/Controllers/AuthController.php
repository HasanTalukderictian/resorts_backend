<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // পাসওয়ার্ড চেক এবং ইউজার ভেরিফিকেশন
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'আপনার দেওয়া তথ্যগুলো সঠিক নয়।'
            ], 401);
        }

        // সফল হলে টোকেন তৈরি (যদি Sanctum ব্যবহার করেন)
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'লগইন সফল হয়েছে!',
            'user' => $user,
            'token' => $token, // React-এ এই টোকেনটি LocalStorage-এ সেভ করবেন
        ], 200);
    }

    // লগআউট প্রসেস (API)
    public function logout(Request $request)
    {
        // ইউজারের বর্তমান টোকেন ডিলিট করে দেওয়া
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'সফলভাবে লগআউট হয়েছে।'
        ], 200);
    }
}

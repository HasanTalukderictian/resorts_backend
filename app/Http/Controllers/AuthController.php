<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DBUser;
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

    // =========================
    // 🔍 1st TABLE: users
    // =========================
    $user = User::where('email', $request->email)->first();

    // =========================
    // 🔍 2nd TABLE: dbusers
    // =========================
    if (!$user) {
        $user = DBUser::where('email', $request->email)->first();
    }

    // =========================
    // ❌ NOT FOUND
    // =========================
    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'Invalid credentials'
        ], 401);
    }

    // =========================
    // 🔐 PASSWORD CHECK
    // =========================
    if (!Hash::check($request->password, $user->password)) {
        return response()->json([
            'status' => 'error',
            'message' => 'Invalid credentials'
        ], 401);
    }

    // =========================
    // 🔥 ROLE CHECK (if exists)
    // =========================
    if (isset($user->role)) {
        if (!in_array($user->role, ['Admin', 'User'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied'
            ], 403);
        }
    }

    // =========================
    // 🔥 TOKEN RESET (SAFE LOGIN)
    // =========================
    if (method_exists($user, 'tokens')) {
        $user->tokens()->delete();
    }

    // =========================
    // 🔑 CREATE TOKEN
    // =========================
    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'status' => 'success',
        'message' => 'Login successful',
        'user' => $user,
        'token' => $token
    ]);
}

    // লগআউট প্রসেস (API)
    public function logout(Request $request)
{
    $user = $request->user();

    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'Unauthenticated'
        ], 401);
    }

    // সব token delete (safe logout)
    $user->tokens()->delete();

    return response()->json([
        'status' => 'success',
        'message' => 'Successfully logged out'
    ], 200);
}
}

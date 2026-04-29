<?php

namespace App\Http\Controllers;

use App\Models\DBUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // ✅ Get Users (Pagination + Safe)
    public function index()
    {
        try {
            $users = DBUser::select('id', 'name', 'email', 'role', 'status', 'created_at')
                ->latest()
                ->paginate(10);

            return response()->json($users);

        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['message' => 'Failed to fetch users'], 500);
        }
    }

    // ✅ Store User (WITH PASSWORD SUPPORT)
    public function store(Request $request)
    {
        try {
            if (auth()->user()->role !== 'Admin') {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'required|email:rfc,dns|unique:dbusers,email',
                'role'     => 'required|in:User,Admin',
                'password' => 'nullable|string|min:6'
            ]);

            DB::beginTransaction();

            $user = DBUser::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'role'     => $validated['role'],
                'status'   => 'Active',
                'password' => Hash::make($validated['password'] ?? '123456') // default password
            ]);

            DB::commit();

            return response()->json([
                'message' => 'User created successfully',
                'data' => $user
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);

            return response()->json([
                'message' => 'User creation failed'
            ], 500);
        }
    }

    // ✅ Update User (SAFE + PASSWORD OPTIONAL)
    public function update(Request $request, $id)
    {
        try {
            if (auth()->user()->role !== 'Admin') {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $user = DBUser::findOrFail($id);

            $validated = $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'required|email:rfc,dns|unique:dbusers,email,' . $id,
                'role'     => 'required|in:User,Admin',
                'status'   => 'required|in:Active,Pending,Inactive',
                'password' => 'nullable|string|min:6'
            ]);

            $data = [
                'name'   => $validated['name'],
                'email'  => $validated['email'],
                'role'   => $validated['role'],
                'status' => $validated['status']
            ];

            // password update optional
            if (!empty($validated['password'])) {
                $data['password'] = Hash::make($validated['password']);
            }

            $user->update($data);

            return response()->json([
                'message' => 'User updated successfully',
                'data' => $user
            ]);

        } catch (\Exception $e) {
            Log::error($e);

            return response()->json([
                'message' => 'Update failed'
            ], 500);
        }
    }

    // ✅ Delete User (Safe)
    public function destroy($id)
    {
        try {
            if (auth()->user()->role !== 'Admin') {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $user = DBUser::findOrFail($id);
            $user->delete();

            return response()->json([
                'message' => 'User deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error($e);

            return response()->json([
                'message' => 'Delete failed'
            ], 500);
        }
    }
}

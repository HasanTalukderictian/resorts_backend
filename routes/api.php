<?php

use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [AuthController::class, 'login']);

// প্রোটেক্টেড রাউট (লগইন করা ছাড়া এক্সেস করা যাবে না)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});



Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store'])->middleware('throttle:10,1');
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});

Route::prefix('v1')->group(function () {

    // পাবলিক রাউট (React Front-end এর জন্য)
    Route::get('/banners/active', [BannerController::class, 'getActiveBanner']);

    // প্রটেক্টেড রাউট (Admin Panel এর জন্য - চাইলে এখানে auth:sanctum মিডলওয়্যার দিতে পারো)
    Route::post('/banners', [BannerController::class, 'store']);

    Route::post('banners/{id}', [BannerController::class, 'update']);
    Route::delete('del-banners/{id}', [BannerController::class, 'destroy']);
    // ভবিষ্যতে আরও রাউট অ্যাড করতে পারো যেমন:
    // Route::get('/banners', [BannerController::class, 'index']);
    // Route::delete('/banners/{id}', [BannerController::class, 'destroy']);
});

<?php

use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\InvestmentController;
use App\Http\Controllers\Api\InvestmentPackageController;
use App\Http\Controllers\Api\WelcomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FeaturesAboutController;
use App\Http\Controllers\FeaturesController;
use App\Http\Controllers\PropertyBenifitController;
use App\Http\Controllers\PropertyOfferController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VideoController;
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
    Route::post('/add-banners', [BannerController::class, 'store']);

    Route::post('update-banners/{id}', [BannerController::class, 'update']);
    Route::delete('del-banners/{id}', [BannerController::class, 'destroy']);
    // ভবিষ্যতে আরও রাউট অ্যাড করতে পারো যেমন:
    // Route::get('/banners', [BannerController::class, 'index']);
    // Route::delete('/banners/{id}', [BannerController::class, 'destroy']);
});


Route::get('/get-features', [FeaturesController::class, 'index']);
Route::post('/add-features', [FeaturesController::class, 'store']);
Route::post('/save-about-features', [FeaturesAboutController::class, 'storeAboutFeatures']);
Route::get('/get-about-features', [FeaturesAboutController::class, 'index']);


Route::get('/get-welcomes', [WelcomeController::class, 'index']);
Route::post('/add-welcomes', [WelcomeController::class, 'store']);
Route::delete('/del-welcomes/{id}', [WelcomeController::class, 'destroy']);

Route::get('/get-videos', [VideoController::class, 'index']);
Route::post('/add-videos', [VideoController::class, 'store']);
Route::delete('/del-videos/{id}', [VideoController::class, 'destroy']);


Route::post('/add-property-offers', [PropertyOfferController::class, 'store']);
Route::post('/edit-property-offers/{id}', [PropertyOfferController::class, 'update']);
Route::delete('/del-property-offers/{id}', [PropertyOfferController::class, 'destroy']);
Route::get('/get-property-offers', [PropertyOfferController::class, 'index']);
Route::get('/gets-property-offers', [PropertyOfferController::class, 'getall']);



Route::post('/add-property-benifit', [PropertyBenifitController::class, 'store']);
Route::post('/edit-property-benifit/{id}', [PropertyBenifitController::class, 'update']);
Route::delete('/delete-property-benifit/{id}', [PropertyBenifitController::class, 'destroy']);
Route::get('/get-property-benifit', [PropertyBenifitController::class, 'index']);


Route::post('/add-investment', [InvestmentController::class, 'store']);
Route::post('/edit-investment/{id}', [InvestmentController::class, 'update']);
Route::delete('/del-investment/{id}', [InvestmentController::class, 'destroy']);
Route::get('/get-investment', [InvestmentController::class, 'index']);

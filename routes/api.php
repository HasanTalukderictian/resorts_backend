<?php

use App\Http\Controllers\Api\AchievementController;
use App\Http\Controllers\Api\AffiliateController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\GalleryController;
use App\Http\Controllers\Api\InvestmentBenefitController;
use App\Http\Controllers\Api\InvestmentController;
use App\Http\Controllers\Api\InvestmentPackageController;
use App\Http\Controllers\Api\InvestReordController;
use App\Http\Controllers\Api\LuxuryItemController;
use App\Http\Controllers\Api\NoticeController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\PartnerController;
use App\Http\Controllers\Api\TeamMemberController;
use App\Http\Controllers\Api\TestominalController;
use App\Http\Controllers\Api\WelcomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FeaturesAboutController;
use App\Http\Controllers\FeaturesController;
use App\Http\Controllers\PropertyBenifitController;
use App\Http\Controllers\PropertyOfferController;
use App\Http\Controllers\QueryController;
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


Route::prefix('events')->group(function () {
    // Public routes
    Route::get('/', [EventController::class, 'index']);
    Route::get('/all', [EventController::class, 'getAllEvents']);
    Route::get('/{identifier}', [EventController::class, 'show']);

    // Protected routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [EventController::class, 'store']);
        Route::put('/{id}', [EventController::class, 'update']);
        Route::delete('/{id}', [EventController::class, 'destroy']);
        Route::post('/{id}/restore', [EventController::class, 'restore']);
        Route::patch('/{id}/status', [EventController::class, 'updateStatus']);
    });
});


Route::prefix('luxury-items')->group(function () {

    Route::get('/', [LuxuryItemController::class, 'index']);

    Route::post('/', [LuxuryItemController::class, 'store']);

    Route::get('/{id}', [LuxuryItemController::class, 'show']);

    Route::put('/{id}', [LuxuryItemController::class, 'update']);

    Route::delete('/{id}', [LuxuryItemController::class, 'destroy']);

    Route::patch('/status/{id}', [LuxuryItemController::class, 'changeStatus']);
});


Route::get('/get-testimonials', [TestominalController::class, 'index']);
Route::post('/add-testimonial', [TestominalController::class, 'store']);
Route::delete('/del-testimonial/{id}', [TestominalController::class, 'destroy']);


Route::post('/investment-benefits', [InvestmentBenefitController::class, 'store']);
Route::get('/get-investment-benefits', [InvestmentBenefitController::class, 'index']);
Route::post('/edit-investment-benefits/{id}', [InvestmentBenefitController::class, 'update']);
Route::get('/del-investment-benefits/{id}', [InvestmentBenefitController::class, 'destroy']);


Route::get('/get-achievement', [AchievementController::class, 'index']);     // GET: সব achievement দেখাবে
Route::post('/add-achievement', [AchievementController::class, 'store']);    // POST: নতুন achievement যোগ করবে
Route::post('/edit-achievement/{id}', [AchievementController::class, 'update']); // POST: achievement আপডেট করবে
Route::delete('/del-achievement/{id}', [AchievementController::class, 'destroy']); // DELETE: achievement ডিলিট করবে


// In routes/api.php
Route::get('/get-investrecord', [InvestReordController::class, 'index']);     // GET all records
Route::post('/add-investrecord', [InvestReordController::class, 'store']);    // POST add record
Route::post('/edit-investrecord/{id}', [InvestReordController::class, 'update']); // POST update record
Route::delete('/del-investrecord/{id}', [InvestReordController::class, 'destroy']); // DELETE record


Route::get('/gallery', [GalleryController::class, 'index']);
Route::post('/gallery', [GalleryController::class, 'store']);
Route::post('/gallery/{id}', [GalleryController::class, 'update']); // Update-er jonno POST use kora hoy file upload thakle
Route::delete('/gallery/{id}', [GalleryController::class, 'destroy']);


Route::get('/blogs', [BlogController::class, 'index']);

Route::post('/blogs', [BlogController::class, 'store']);

Route::post('/blogs/{id}', [BlogController::class, 'update']);
Route::delete('/blogs/{id}', [BlogController::class, 'destroy']);
Route::get('/blogs/{id}', [BlogController::class, 'show']);


Route::prefix('notices')->group(function () {
    Route::get('/', [NoticeController::class, 'index']); // Get all notices
    Route::post('/', [NoticeController::class, 'store']); // Create new notice
    Route::get('/active', [NoticeController::class, 'getActiveNotices']); // Get active notices
    Route::get('/{id}', [NoticeController::class, 'show']); // Get single notice
    Route::post('/{id}', [NoticeController::class, 'update']); // Update notice
    Route::delete('/{id}', [NoticeController::class, 'destroy']); // Delete notice

});


Route::get('/packages', [PackageController::class, 'index']);

Route::post('/packages', [PackageController::class, 'store']);

Route::put('/packages/{id}', [PackageController::class, 'update']);

Route::delete('/packages/{id}', [PackageController::class, 'destroy']);

Route::get('/packages/{id}/history', [PackageController::class, 'priceHistory']);


Route::prefix('affiliates')->group(function () {
    Route::get('/', [AffiliateController::class, 'index'])->name('affiliates.index');
    Route::post('/', [AffiliateController::class, 'store'])->name('affiliates.store');
    Route::get('/{id}', [AffiliateController::class, 'show'])->name('affiliates.show');
    Route::put('/{id}', [AffiliateController::class, 'update'])->name('affiliates.update');
    Route::delete('/{id}', [AffiliateController::class, 'destroy'])->name('affiliates.destroy');
    Route::patch('/{id}/status', [AffiliateController::class, 'updateStatus'])->name('affiliates.status');
    Route::post('/{id}/click', [AffiliateController::class, 'trackClick'])->name('affiliates.click');
    Route::post('/bulk-delete', [AffiliateController::class, 'bulkDelete'])->name('affiliates.bulk-delete');
});


Route::get('/partners', [PartnerController::class, 'index']);
Route::get('/partners/active', [PartnerController::class, 'activePartners']);
Route::get('/partners/{id}', [PartnerController::class, 'show']);

Route::post('/partners', [PartnerController::class, 'store']);
Route::post('/partners/{id}', [PartnerController::class, 'update']);

Route::delete('/partners/{id}', [PartnerController::class, 'destroy']);

Route::post('/partners/{id}/click', [PartnerController::class, 'incrementClick']);



Route::get('/queries', [QueryController::class, 'index']);
Route::post('/addqueries', [QueryController::class, 'store']);
Route::get('/queries/{id}', [QueryController::class, 'show']);
Route::delete('/queries/{id}', [QueryController::class, 'destroy']);



Route::get('/team-members', [TeamMemberController::class, 'index']);

Route::post('/add-team-member', [TeamMemberController::class, 'store']);

Route::post('/edit-team-member/{id}', [TeamMemberController::class, 'update']);

Route::delete('/delete-team-member/{id}', [TeamMemberController::class, 'destroy']);

Route::get('/team-member/{id}', [TeamMemberController::class, 'show']);

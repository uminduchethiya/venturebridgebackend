<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StartupController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\InvestorController;
use App\Http\Controllers\DashboardController;
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

Route::post('/register', [AuthController::class, 'register']);
Route::get('/registerindex', [AuthController::class, 'registrationindex']);


Route::post('/login', [AuthController::class, 'login']);
Route::get('/loginindex', [AuthController::class, 'loginindex']);



Route::get('/forgotpasswordindex', [AuthController::class, 'forgotPasswordIndex']);
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail']);


Route::middleware('auth:sanctum')->post('/startup', [StartupController::class, 'storeStartup']);
Route::middleware('auth:sanctum')->post('/investor', [InvestorController::class, 'storeInvestor']);
Route::middleware('auth:sanctum')->get('/users', [UserController::class, 'getUsersList']);
Route::middleware('auth:sanctum')->get('/users', [UserController::class, 'getUsersList']);
Route::get('/payments', [PaymentController::class, 'getPaymentList']);
Route::get('/documents', [DocumentController::class, 'getAllDocuments']);
Route::get('/dashboard', [DashboardController::class, 'dashboardSummary']);
Route::get('/reset-password/{token}', function ($token) {
    return view('auth.reset-password', ['token' => $token]);
})->name('password.reset');

Route::middleware(['auth:sanctum'])->post('/check-startup-match', [InvestorController::class, 'checkStartupMatch']);
// In routes/api.php
Route::middleware(['auth:sanctum'])->get('/investor/notifications', [InvestorController::class, 'getInvestorNotifications']);

Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::post('/packages', [PackageController::class, 'store']);
    Route::get('/packages', [PackageController::class, 'index']);
    Route::delete('/packages/{id}', [PackageController::class, 'destroy']);
    Route::get('/packages/{id}', [PackageController::class, 'show']);
    Route::put('/packages/{id}', [PackageController::class, 'update']);

});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/posts', [PostController::class, 'store']);
    Route::post('/activate-package', [PackageController::class, 'activate']);
    Route::get('/active-package', [PackageController::class, 'getActivePackage']);
    Route::get('/posts', [PostController::class, 'fetchPosts']);

});

Route::middleware('auth:sanctum')->post('/create-checkout-session', [StripeController::class, 'createCheckoutSession']);
// Route::middleware('auth:sanctum')->get('/payment-success', [StripeController::class, 'paymentSuccess']);
Route::get('/payment-success', [StripeController::class, 'paymentSuccess']);
Route::middleware('auth:sanctum')->post('/create-checkout-session', [StripeController::class, 'createCheckoutSession']);

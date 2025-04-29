<?php

use App\Http\Controllers\InvestorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StartupController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\DocumentController;
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

Route::post('/check-startup-match', [InvestorController::class, 'checkStartupMatch']);

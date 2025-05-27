<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BusinessController as AdminBusinessController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Business\ServiceController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;

// Public auth routes
Route::post('login',    [AuthController::class,'login']);
Route::post('register', [AuthController::class,'register']);

Route::middleware(['auth:sanctum','admin'])->get('/user', function (Request $request) {
    return $request->user();
});


// Any authenticated user can logout
Route::middleware('auth:sanctum')->post('logout',[AuthController::class,'logout']);


// ─── ADMIN ──────────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum','admin'])->prefix('admin')->group(function(){
    Route::get('businesses',        [AdminBusinessController::class,'index']);
    Route::get('businesses/{id}',   [AdminBusinessController::class,'show']);
    Route::post('businesses',       [AdminBusinessController::class,'store']);
    Route::put('businesses/{id}',   [AdminBusinessController::class,'update']);
    Route::delete('businesses/{id}',[AdminBusinessController::class,'destroy']);

    Route::get('users',             [AdminUserController::class,'index']);
    Route::get('/users/{id}', [AdminUserController::class, 'show']);
    Route::post('users',            [AdminUserController::class,'store']);
    Route::put('/users/{id}', [AdminUserController::class, 'update']);
    Route::delete('users/{id}',     [AdminUserController::class,'destroy']);
});


// ─── BUSINESS ────────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum','business'])->prefix('business')->group(function(){
    Route::get('services',          [ServiceController::class,'index']);
    Route::get('services/{id}',     [ServiceController::class,'show']);
    Route::post('services',         [ServiceController::class,'store']);
    Route::put('services/{id}',     [ServiceController::class,'update']);
    Route::delete('services/{id}',  [ServiceController::class,'destroy']);
});


// ─── USER ────────────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum','user'])->prefix('user')->group(function(){
    Route::get('bookings',            [BookingController::class,'index']);
    Route::get('bookings/{id}',       [BookingController::class,'show']);
    Route::post('bookings',           [BookingController::class,'store']);
    Route::put('bookings/{id}',       [BookingController::class,'update']);
    Route::delete('bookings/{id}',    [BookingController::class,'destroy']);

    Route::get('reviews',                    [ReviewController::class,'index']);
    Route::get('businesses', [AdminBusinessController::class, 'user']);
    Route::get('reviews/{id}',               [ReviewController::class,'show']);
    Route::get('reviews/business/{business}',[ReviewController::class,'business_review']);
    Route::post('reviews',                   [ReviewController::class,'store']);
    Route::put('reviews/{id}',               [ReviewController::class,'update']);
    Route::delete('reviews/{id}',            [ReviewController::class,'destroy']);
});

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthManager;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MechanicController;
use App\Models\User;

// API Routes
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Login route
Route::post('/login', [AuthController::class, 'login']);

// Admin specific routes
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('vehicles', VehicleController::class);
    Route::apiResource('appointments', AppointmentController::class);
    Route::apiResource('logs', LogController::class);
});

// Logs view for clients and mechanics
Route::middleware(['auth:sanctum', 'role:client,mechanic'])->group(function () {
    Route::get('/my-logs', [LogController::class, 'clientLogs']);
});

// Mechanic specific routes
Route::middleware(['auth:sanctum', 'role:mechanic'])->group(function () {
    Route::get('/mechanic/logs', [LogController::class, 'mechanicLogs']);
    Route::get('/mechanic/logs/{id}', [LogController::class, 'mechanicShow']);
    Route::post('/mechanic/logs', [LogController::class, 'storeAsMechanic']);
    Route::put('/mechanic/logs/{id}', [LogController::class, 'updateAsMechanic']);
    Route::patch('/mechanic/logs/{id}', [LogController::class, 'updateAsMechanic']);
    Route::delete('/mechanic/logs/{id}', [LogController::class, 'destroyAsMechanic']);

    Route::get('/lookup/clients', [ClientController::class, 'index']);
    Route::get('/lookup/vehicles', [VehicleController::class, 'index']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('mechanics', MechanicController::class);
});

Route::get('/debug/users', function () {
    return User::select('id','email','role')->orderBy('id')->limit(5)->get();
});

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
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

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


// Debug routes - to be removed in production
Route::get('/debug/users', function () {
    return User::select('id','email','role')->orderBy('id')->limit(5)->get();
});

Route::get('/debug/db', function () {
    return [
        'default' => config('database.default'),
        'url'     => env('DATABASE_URL'),
        'ok'      => DB::select('SELECT 1 as ok'),
    ];
});

// Lista tablas existentes
Route::get('/debug/tables', function () {
    $tables = DB::select("SELECT tablename FROM pg_tables WHERE schemaname='public' ORDER BY tablename");
    return array_map(fn($t) => $t->tablename, $tables);
});

// Ejecuta migraciones
Route::post('/debug/migrate', function () {
    Artisan::call('config:clear');
    Artisan::call('migrate', ['--force' => true]);
    return response()->json(['output' => Artisan::output()]);
});

// Ejecuta el seeder principal o el tuyo específico
Route::post('/debug/seed', function () {
    Artisan::call('db:seed', [
        '--class' => 'Database\\Seeders\\AutoShopSeeder',
        '--force' => true
    ]);
    return response()->json(['output' => Artisan::output()]);
});

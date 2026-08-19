<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Authentication routes (tanpa middleware sementara)
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/profile', [AuthController::class, 'profile']);
Route::post('/refresh', [AuthController::class, 'refresh']);
Route::get('/permissions', [AuthController::class, 'checkPermissions']);

// Example of role-based routes (tanpa middleware)
// Store Manager routes
Route::get('/store-manager/dashboard', function () {
    return response()->json([
        'message' => 'Store Manager Dashboard',
        'data' => [
            'total_sales' => 1500000,
            'total_transactions' => 45,
            'top_products' => ['Product A', 'Product B', 'Product C']
        ]
    ]);
});

// Area Manager routes  
Route::get('/area-manager/dashboard', function () {
    return response()->json([
        'message' => 'Area Manager Dashboard',
        'data' => [
            'total_stores' => 5,
            'total_sales' => 7500000,
            'store_performance' => [
                'Store 1' => 1500000,
                'Store 2' => 1200000,
                'Store 3' => 1800000,
                'Store 4' => 1400000,
                'Store 5' => 1600000
            ]
        ]
    ]);
});

// Kasir routes
Route::get('/kasir/dashboard', function () {
    return response()->json([
        'message' => 'Kasir Dashboard',
        'data' => [
            'today_sales' => 250000,
            'transaction_count' => 15,
            'recent_transactions' => [
                ['id' => 1, 'amount' => 50000, 'customer' => 'John Doe'],
                ['id' => 2, 'amount' => 75000, 'customer' => 'Jane Smith'],
                ['id' => 3, 'amount' => 35000, 'customer' => 'Bob Johnson']
            ]
        ]
    ]);
});

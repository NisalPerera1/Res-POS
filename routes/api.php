<?php

use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Health check
Route::get('/health', fn() => response()->json(['status' => 'ok', 'timestamp' => now()]));

// Public routes
Route::post('/login-pin',  [UserController::class, 'loginPin']);
Route::post('/logout',     [UserController::class, 'logout']);
Route::get('/users/list',  [UserController::class, 'listUsers']);

Route::middleware('auth:sanctum')->group(function () {

    // ── Menu ──────────────────────────────────────────────
    Route::get('/menu',                              [MenuController::class, 'fullMenu']);
    Route::get('/menu/categories',                   [MenuController::class, 'categories']);
    Route::get('/menu/items',                        [MenuController::class, 'items']);
    Route::post('/menu/categories',                  [MenuController::class, 'storeCategory']);
    Route::put('/menu/categories/{id}',              [MenuController::class, 'updateCategory']);
    Route::delete('/menu/categories/{id}',           [MenuController::class, 'destroyCategory']);
    Route::post('/menu/items',                       [MenuController::class, 'storeItem']);
    Route::put('/menu/items/{id}',                   [MenuController::class, 'updateItem']);
    Route::patch('/menu/items/{id}/toggle-availability', [MenuController::class, 'toggleAvailability']);

    // ── Tables ────────────────────────────────────────────
    Route::get('/tables',        [TableController::class, 'index']);
    Route::post('/tables',       [TableController::class, 'store']);
    Route::get('/tables/{id}',   [TableController::class, 'show']);
    Route::put('/tables/{id}',   [TableController::class, 'update']);
    Route::delete('/tables/{id}',[TableController::class, 'destroy']);

    // ── Orders ────────────────────────────────────────────
    Route::get('/orders',          [OrderController::class, 'index']);
    Route::post('/orders',         [OrderController::class, 'store']);
    Route::get('/orders/{id}',     [OrderController::class, 'show']);

    // Items
    Route::post('/orders/{id}/items',                    [OrderController::class, 'addItem']);
    Route::patch('/orders/{id}/items/{itemId}',          [OrderController::class, 'updateItem']);
    // ✅ BOTH patch and delete for void — covers all cases
    Route::patch('/orders/{id}/items/{itemId}/void',     [OrderController::class, 'voidItem']);
    Route::delete('/orders/{id}/items/{itemId}/void',    [OrderController::class, 'voidItem']);

    // Order actions
    Route::post('/orders/{id}/kot',       [OrderController::class, 'sendKOT']);
    Route::patch('/orders/{id}/status',   [OrderController::class, 'updateStatus']);
    Route::post('/orders/{id}/payments',  [OrderController::class, 'processPayment']);
    Route::post('/orders/{id}/discount',  [OrderController::class, 'applyDiscount']);
});
<?php

use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;   // ← ADD THIS
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StaffController;

// Health check
Route::get('/health', fn() => response()->json(['status' => 'ok', 'timestamp' => now()]));

// Public routes
Route::post('/login-pin',  [UserController::class, 'loginPin']);
Route::post('/logout',     [UserController::class, 'logout']);
Route::get('/users/list',  [UserController::class, 'listUsers']);

Route::middleware('auth:sanctum')->group(function () {

    // ── Menu ──────────────────────────────────────────────────
    Route::get('/menu',                                  [MenuController::class, 'fullMenu']);
    Route::get('/menu/categories',                       [MenuController::class, 'categories']);
    Route::post('/menu/categories',                      [MenuController::class, 'storeCategory']);
    Route::put('/menu/categories/{id}',                  [MenuController::class, 'updateCategory']);
    Route::delete('/menu/categories/{id}',               [MenuController::class, 'destroyCategory']);

    Route::get('/menu/items',                            [MenuController::class, 'items']);
    Route::get('/menu/items/{id}',                       [MenuController::class, 'showItem']);
    Route::post('/menu/items',                           [MenuController::class, 'storeItem']);
    Route::put('/menu/items/{id}',                       [MenuController::class, 'updateItem']);
    Route::delete('/menu/items/{id}',                    [MenuController::class, 'destroyItem']);
    Route::patch('/menu/items/{id}/toggle-availability', [MenuController::class, 'toggleAvailability']);
    Route::patch('/menu/items/{id}/toggle',              [MenuController::class, 'toggleAvailability']);

    // ── Modifier Groups ────────────────────────────────────────
    Route::get('/modifier-groups',                       [MenuController::class, 'modifierGroups']);
    Route::get('/modifier-groups/{id}',                  [MenuController::class, 'showModifierGroup']);
    Route::post('/modifier-groups',                      [MenuController::class, 'storeModifierGroup']);
    Route::put('/modifier-groups/{id}',                  [MenuController::class, 'updateModifierGroup']);
    Route::delete('/modifier-groups/{id}',               [MenuController::class, 'destroyModifierGroup']);

    // ── Modifiers ──────────────────────────────────────────────
    Route::post('/modifiers',                            [MenuController::class, 'storeModifier']);
    Route::put('/modifiers/{id}',                        [MenuController::class, 'updateModifier']);
    Route::delete('/modifiers/{id}',                     [MenuController::class, 'destroyModifier']);

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
    Route::post('/orders/{id}/discount',  [OrderController::class, 'applyDiscount']);

    // Kitchen display routes
    Route::get('/kitchen/tables/{tableId}/items', [OrderController::class, 'getKitchenItemsByTable']);
    Route::get('/kitchen/items', [OrderController::class, 'getAllKitchenItems']);

    Route::post('/orders/direct', [OrderController::class, 'storeDirect']);
    Route::patch('/orders/{id}/customer', [OrderController::class, 'updateCustomer']);


    // Modifier Groups
    Route::get('/modifier-groups',          [MenuController::class, 'modifierGroups']);
    Route::post('/modifier-groups',         [MenuController::class, 'storeModifierGroup']);
    Route::put('/modifier-groups/{id}',     [MenuController::class, 'updateModifierGroup']);

    // Payments
    Route::post('/orders/{id}/payments',  [PaymentController::class, 'processPayment']);
    Route::get('/orders/{id}/receipt',    [PaymentController::class, 'receipt']);
    Route::get('/payments',               [PaymentController::class, 'index']);
    Route::get('/payments/summary',       [PaymentController::class, 'summary']);
    Route::patch('/orders/{id}/service-charge', [OrderController::class, 'updateServiceCharge']);
    
    Route::get('/reports/summary', [ReportController::class, 'summary']);
    Route::get('/reports/today',   [ReportController::class, 'today']);

    Route::prefix('staff')->group(function () {
        Route::get('/overview',               [StaffController::class, 'overview']);
        Route::get('/leaves',                 [StaffController::class, 'allLeaves']); // add this method
        Route::get('/payroll-list',           [StaffController::class, 'payrollList']); // add this method
        Route::post('/generate-all-payrolls', [StaffController::class, 'generateAllPayrolls']); // add this method
        Route::get('/',                       [StaffController::class, 'index']);
        Route::post('/',                      [StaffController::class, 'store']);
        Route::get('/{id}',                   [StaffController::class, 'show']);
        Route::put('/{id}',                   [StaffController::class, 'update']);
        Route::patch('/{id}/toggle-active',   [StaffController::class, 'toggleActive']);
        Route::post('/{id}/clock-in',         [StaffController::class, 'clockIn']);
        Route::post('/{id}/clock-out',        [StaffController::class, 'clockOut']);
        Route::get('/{id}/shifts',            [StaffController::class, 'shifts']);
        Route::post('/{id}/shifts',           [StaffController::class, 'storeShift']);
        Route::patch('/{id}/shifts/{shiftId}',[StaffController::class, 'updateShift']);
        Route::get('/{id}/payrolls',          [StaffController::class, 'payrolls']);
        Route::post('/{id}/calculate-payroll',[StaffController::class, 'calculatePayroll']);
        Route::patch('/{id}/payroll/{payrollId}', [StaffController::class, 'updatePayroll']);
        Route::get('/{id}/leave',             [StaffController::class, 'leaveRequests']);
        Route::post('/{id}/leave',            [StaffController::class, 'storeLeave']);
        Route::patch('/{id}/leave/{leaveId}', [StaffController::class, 'updateLeave']);
    Route::get('/overview',               [StaffController::class, 'overview']);
    Route::get('/leaves',                 [StaffController::class, 'allLeaves']); // add this method
    Route::get('/payroll-list',           [StaffController::class, 'payrollList']); // add this method
    Route::post('/generate-all-payrolls', [StaffController::class, 'generateAllPayrolls']); // add this method
    Route::get('/',                       [StaffController::class, 'index']);
    Route::post('/',                      [StaffController::class, 'store']);
    Route::get('/{id}',                   [StaffController::class, 'show']);
    Route::put('/{id}',                   [StaffController::class, 'update']);
    Route::patch('/{id}/toggle-active',   [StaffController::class, 'toggleActive']);
    Route::post('/{id}/clock-in',         [StaffController::class, 'clockIn']);
    Route::post('/{id}/clock-out',        [StaffController::class, 'clockOut']);
    Route::get('/{id}/shifts',            [StaffController::class, 'shifts']);
    Route::post('/{id}/shifts',           [StaffController::class, 'storeShift']);
    Route::patch('/{id}/shifts/{shiftId}',[StaffController::class, 'updateShift']);
    Route::get('/{id}/payrolls',          [StaffController::class, 'payrolls']);
    Route::post('/{id}/calculate-payroll',[StaffController::class, 'calculatePayroll']);
    Route::patch('/{id}/payroll/{payrollId}', [StaffController::class, 'updatePayroll']);
    Route::get('/{id}/leave',             [StaffController::class, 'leaveRequests']);
    Route::post('/{id}/leave',            [StaffController::class, 'storeLeave']);
    Route::patch('/{id}/leave/{leaveId}', [StaffController::class, 'updateLeave']);
});

    });
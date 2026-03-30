<?php

use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DirectOrderController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ModifierPricingController;

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
    Route::get('/menu/items/{id}/modifiers',           [MenuController::class, 'itemModifiers']);
    Route::get('/menu/items/{item}/modifier-pricing',    [ModifierPricingController::class, 'index']);
    Route::patch('/menu/items/{item}/modifier-pricing',   [ModifierPricingController::class, 'update']);
    Route::get('/menu/items/{item}/price-preview',   [OrderController::class, 'pricePreview']);
    Route::post('/menu/items',                           [MenuController::class, 'storeItem']);
    Route::post('/menu/items/upload-image',                [MenuController::class, 'uploadItemImage']);
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
    // Delete route for removing items completely
    Route::delete('/orders/{id}/items/{itemId}',         [OrderController::class, 'removeItem']);

    // Direct Order Management
Route::get('/direct-orders/pending', [DirectOrderController::class, 'getPendingOrders']);
Route::get('/direct-orders/{id}', [DirectOrderController::class, 'getOrder']);
Route::post('/direct-orders', [DirectOrderController::class, 'createOrder']);
Route::post('/direct-orders/{id}/switch', [DirectOrderController::class, 'switchOrder']);
Route::patch('/direct-orders/{id}/customer', [DirectOrderController::class, 'updateCustomer']);
Route::patch('/direct-orders/{id}/type', [DirectOrderController::class, 'updateType']);
Route::post('/direct-orders/{id}/cancel', [DirectOrderController::class, 'cancelOrder']);
Route::delete('/direct-orders/{id}', [DirectOrderController::class, 'deleteOrder']);

// Order KOT operations
    Route::post('/orders/{id}/send-kot', [OrderController::class, 'sendKOT']);
    Route::post('/orders/{id}/print-kot', [OrderController::class, 'printKOT']);
    Route::patch('/orders/{id}/status',   [OrderController::class, 'updateStatus']);
    Route::post('/orders/{id}/payments',  [PaymentController::class, 'processPayment']);
    Route::get('/orders/{id}/receipt',    [PaymentController::class, 'receipt']);
    Route::post('/orders/{id}/discount',  [OrderController::class, 'applyDiscount']);
    
    // Kitchen display routes
    Route::get('/kitchen/tables/{tableId}/items', [OrderController::class, 'getKitchenItemsByTable']);
    Route::get('/kitchen/items', [OrderController::class, 'getAllKitchenItems']);

    Route::post('/orders/direct', [OrderController::class, 'storeDirect']);
    Route::patch('/orders/{id}/customer', [OrderController::class, 'updateCustomer']);
    
    // Reports
    Route::get('/reports/summary', [ReportController::class, 'summary']);
    Route::get('/reports/today',   [ReportController::class, 'today']);
    Route::get('/reports/transactions', [ReportController::class, 'transactions']);

    // ── Staff ────────────────────────────────────────────
    Route::prefix('staff')->group(function () {
        Route::get('/overview',               [StaffController::class, 'overview']);
        Route::get('/leaves',                 [StaffController::class, 'allLeaves']); 
        Route::get('/payroll-list',           [StaffController::class, 'payrollList']); 
        Route::post('/generate-all-payrolls', [StaffController::class, 'generateAllPayrolls']); 
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

    // Payments
    Route::get('/payments',               [PaymentController::class, 'index']);
    Route::get('/payments/summary',       [PaymentController::class, 'summary']);
    Route::patch('/orders/{id}/service-charge', [OrderController::class, 'updateServiceCharge']);

});

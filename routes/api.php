<?php

use App\Http\Controllers\Management\DashboardController;
use App\Http\Controllers\POS\DirectOrderController;
use App\Http\Controllers\Management\MenuController;
use App\Http\Controllers\Management\ModifierPricingController;
use App\Http\Controllers\POS\OrderController;
use App\Http\Controllers\POS\PaymentController;
use App\Http\Controllers\Management\ReportController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\POS\TableController;
use App\Http\Controllers\Public\UserController;
use App\Http\Controllers\Public\QrOrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ── Health check ─────────────────────────────────────────────
Route::get('/health', fn () => response()->json(['status' => 'ok', 'timestamp' => now()]));

// ── Public auth routes ────────────────────────────────────────
Route::post('/login-pin', [UserController::class, 'loginPin']);
Route::post('/logout',    [UserController::class, 'logout']);
Route::get('/users/list', [UserController::class, 'listUsers']);
Route::put('/users/{id}', [UserController::class, 'update']);

// ── QR ordering (public) ──────────────────────────────────────
Route::prefix('qr')->group(function () {
    Route::post('/orders', [QrOrderController::class, 'store']);
});

// ═══════════════════════════════════════════════════════════════
// STAFF MODULE
//
// FIX 1 — One single group for all staff routes. The old
//   auth:sanctum staff sub-group (~lines 145–195) has been
//   removed. All staff routes now live here.
//
// FIX 2 — Static/named segments come BEFORE wildcard {staff}.
//   Order: /leaves/all, /advances/all, /payrolls/summary,
//   /payrolls/generate, /service-charge-* THEN /{staff}.
//
// FIX 3 — Removed the duplicate anonymous POST /advances
//   closure that was silently overriding storeAdvance().
//
// FIX 4 — The orphaned GET /staff/service-charge public route
//   has been removed. The correct endpoint is
//   GET /staff/service-charge-distribution inside this group.
// ═══════════════════════════════════════════════════════════════
Route::middleware(['cors'])->prefix('staff')->group(function () {

    // ── Leaves (static before wildcard) ──────────────────────
    Route::get('/leaves/all',        [StaffController::class, 'getLeaves']);
    Route::post('/leaves',           [StaffController::class, 'storeLeave']);
    Route::put('/leaves/{leave}',    [StaffController::class, 'updateLeave']);
    Route::delete('/leaves/{leave}', [StaffController::class, 'destroyLeave']);

    // ── Advances (static before wildcard) ────────────────────
    Route::get('/advances/all',          [StaffController::class, 'getAdvances']);
    Route::post('/advances',             [StaffController::class, 'storeAdvance']);
    Route::put('/advances/{advance}',    [StaffController::class, 'updateAdvance']);
    Route::delete('/advances/{advance}', [StaffController::class, 'destroyAdvance']);

    // ── Attendance ────────────────────────────────────────────
    Route::get('/attendance/all', [StaffController::class, 'getAttendance']);
    Route::post('/attendance',    [StaffController::class, 'storeAttendance']);

    // ── Service Charge ──────────────────────────────────────────────
    Route::get('/service-charge-distribution',  [StaffController::class, 'getServiceChargeDistribution']);
    Route::post('/service-charge-distribute',   [StaffController::class, 'distributeServiceCharge']);
    Route::post('/service-charge-pool',         [StaffController::class, 'updateServiceChargePool']);

    // ── Payrolls (summary + generate BEFORE {payroll} wildcard)
    Route::get('/payrolls/summary',      [StaffController::class, 'monthlySummary']);
    Route::get('/payrolls',              [StaffController::class, 'getPayrolls']);
    Route::post('/payrolls/generate',    [StaffController::class, 'generatePayroll']);
    Route::put('/payrolls/{payroll}',    [StaffController::class, 'updatePayroll']);

    // ── Dashboard ─────────────────────────────────────────────
    Route::get('/dashboard', [StaffController::class, 'getDashboard']);

    // ── Staff CRUD (wildcards LAST — after all static routes) ─
    Route::get('/',           [StaffController::class, 'index']);
    Route::post('/',          [StaffController::class, 'store']);
    Route::get('/{staff}',    [StaffController::class, 'show']);
    Route::put('/{staff}',    [StaffController::class, 'update']);
    Route::delete('/{staff}', [StaffController::class, 'destroy']);
});

// ═══════════════════════════════════════════════════════════════
// AUTHENTICATED ROUTES
// ═══════════════════════════════════════════════════════════════
Route::middleware('auth:sanctum')->group(function () {

    // ── Menu ──────────────────────────────────────────────────
    Route::get('/menu',                              [MenuController::class, 'fullMenu']);
    Route::get('/menu/categories',                   [MenuController::class, 'categories']);
    Route::post('/menu/categories',                  [MenuController::class, 'storeCategory']);
    Route::put('/menu/categories/{id}',              [MenuController::class, 'updateCategory']);
    Route::delete('/menu/categories/{id}',           [MenuController::class, 'destroyCategory']);
    Route::get('/menu/items',                        [MenuController::class, 'items']);
    Route::get('/menu/items/{id}',                   [MenuController::class, 'showItem']);
    Route::get('/menu/items/{id}/modifiers',         [MenuController::class, 'itemModifiers']);
    Route::get('/menu/items/{item}/modifier-pricing',[ModifierPricingController::class, 'index']);
    Route::patch('/menu/items/{item}/modifier-pricing',[ModifierPricingController::class, 'update']);
    Route::get('/menu/items/{item}/price-preview',   [OrderController::class, 'pricePreview']);
    Route::post('/menu/items',                       [MenuController::class, 'storeItem']);
    Route::post('/menu/items/upload-image',          [MenuController::class, 'uploadItemImage']);
    Route::post('/menu/items/bulk-import',           [MenuController::class, 'bulkImport']);
    Route::get('/menu/items/import-template',       [MenuController::class, 'downloadImportTemplate']);
    Route::put('/menu/items/{id}',                   [MenuController::class, 'updateItem']);
    Route::delete('/menu/items/{id}',                [MenuController::class, 'destroyItem']);
    Route::patch('/menu/items/{id}/toggle-availability', [MenuController::class, 'toggleAvailability']);
    Route::patch('/menu/items/{id}/toggle',          [MenuController::class, 'toggleAvailability']);

    // ── Modifier Groups ───────────────────────────────────────
    Route::get('/modifier-groups',       [MenuController::class, 'modifierGroups']);
    Route::get('/modifier-groups/{id}',  [MenuController::class, 'showModifierGroup']);
    Route::post('/modifier-groups',      [MenuController::class, 'storeModifierGroup']);
    Route::put('/modifier-groups/{id}',  [MenuController::class, 'updateModifierGroup']);
    Route::delete('/modifier-groups/{id}',[MenuController::class, 'destroyModifierGroup']);

    // ── Modifiers ─────────────────────────────────────────────
    Route::post('/modifiers',      [MenuController::class, 'storeModifier']);
    Route::put('/modifiers/{id}',  [MenuController::class, 'updateModifier']);
    Route::delete('/modifiers/{id}',[MenuController::class, 'destroyModifier']);

    // ── Tables ────────────────────────────────────────────────
    Route::post('/tables/upload-image', [TableController::class, 'uploadImage']);
    Route::get('/tables',          [TableController::class, 'index']);
    Route::post('/tables',         [TableController::class, 'store']);
    Route::get('/tables/{id}',     [TableController::class, 'show']);
    Route::put('/tables/{id}',     [TableController::class, 'update']);
    Route::delete('/tables/{id}',  [TableController::class, 'destroy']);

    // ── Orders ────────────────────────────────────────────────
    Route::get('/orders',              [OrderController::class, 'index']);
    Route::post('/orders',             [OrderController::class, 'store']);
    Route::post('/orders/direct',      [OrderController::class, 'storeDirect']);
    Route::get('/orders/{id}',         [OrderController::class, 'show']);
    Route::post('/orders/{id}/items',  [OrderController::class, 'addItem']);
    Route::patch('/orders/{id}/items/{itemId}',      [OrderController::class, 'updateItem']);
    Route::patch('/orders/{id}/items/{itemId}/void', [OrderController::class, 'voidItem']);
    Route::delete('/orders/{id}/items/{itemId}/void',[OrderController::class, 'voidItem']);
    Route::delete('/orders/{id}/items/{itemId}',     [OrderController::class, 'removeItem']);
    
    // ── Order Item Add-ons ───────────────────────────────────────
    Route::post('/orders/{id}/items/{itemId}/addons',           [OrderController::class, 'addAddon']);
    Route::put('/orders/{id}/items/{itemId}/addons/{addonId}',  [OrderController::class, 'updateAddon']);
    Route::delete('/orders/{id}/items/{itemId}/addons/{addonId}',[OrderController::class, 'deleteAddon']);
    Route::post('/orders/{id}/send-kot',   [OrderController::class, 'sendKOT']);
    Route::post('/orders/{id}/print-kot',  [OrderController::class, 'printKOT']);
    Route::patch('/orders/{id}/status',    [OrderController::class, 'updateStatus']);
    Route::post('/orders/{id}/payments',   [PaymentController::class, 'processPayment']);
    Route::get('/orders/{id}/receipt',     [PaymentController::class, 'receipt']);
    Route::post('/orders/{id}/discount',   [OrderController::class, 'applyDiscount']);
    Route::patch('/orders/{id}/customer',  [OrderController::class, 'updateCustomer']);
    Route::patch('/orders/{id}/service-charge', [OrderController::class, 'updateServiceCharge']);

    // ── Direct orders ─────────────────────────────────────────
    Route::get('/direct-orders/pending',     [DirectOrderController::class, 'getPendingOrders']);
    Route::get('/direct-orders/{id}',        [DirectOrderController::class, 'getOrder']);
    Route::post('/direct-orders',            [DirectOrderController::class, 'createOrder']);
    Route::post('/direct-orders/{id}/switch',[DirectOrderController::class, 'switchOrder']);
    Route::patch('/direct-orders/{id}/customer',[DirectOrderController::class, 'updateCustomer']);
    Route::patch('/direct-orders/{id}/type', [DirectOrderController::class, 'updateType']);
    Route::post('/direct-orders/{id}/cancel',[DirectOrderController::class, 'cancelOrder']);
    Route::delete('/direct-orders/{id}',     [DirectOrderController::class, 'deleteOrder']);
    
    // ── Direct Order Item Add-ons ───────────────────────────────
    Route::post('/direct-orders/{id}/items/{itemId}/addons',           [DirectOrderController::class, 'addAddon']);
    Route::put('/direct-orders/{id}/items/{itemId}/addons/{addonId}',  [DirectOrderController::class, 'updateAddon']);
    Route::delete('/direct-orders/{id}/items/{itemId}/addons/{addonId}',[DirectOrderController::class, 'deleteAddon']);

    // ── Kitchen display ───────────────────────────────────────
    Route::get('/kitchen/tables/{tableId}/items', [OrderController::class, 'getKitchenItemsByTable']);
    Route::get('/kitchen/items',                  [OrderController::class, 'getAllKitchenItems']);

    // ── Dashboard ─────────────────────────────────────────────
    Route::get('/dashboard',              [DashboardController::class, 'index']);
    Route::get('/dashboard/stats',        [DashboardController::class, 'stats']);
    Route::get('/dashboard/recent-orders',[DashboardController::class, 'recentOrders']);
    Route::get('/dashboard/hourly',       [DashboardController::class, 'hourly']);
    Route::get('/dashboard/top-items',    [DashboardController::class, 'topItems']);

    // ── Reports ───────────────────────────────────────────────
    Route::get('/reports/summary',      [ReportController::class, 'summary']);
    Route::get('/reports/today',        [ReportController::class, 'today']);
    Route::get('/reports/transactions', [ReportController::class, 'transactions']);
    Route::get('/reports/tables',       [ReportController::class, 'tables']);
    Route::get('/reports/orders',       [ReportController::class, 'orders']);
    Route::get('/reports/dashboard',    [ReportController::class, 'dashboard']);

    // ── Payments ──────────────────────────────────────────────
    Route::get('/payments',         [PaymentController::class, 'index']);
    Route::get('/payments/summary', [PaymentController::class, 'summary']);
});
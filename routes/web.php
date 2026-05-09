<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\QrOrderController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\MenuController;
use App\Http\Controllers\Web\SpecialsController;

// Public website
Route::get('/', [HomeController::class, 'index'])->name('home');

// QR ordering entrypoint: /menu?table={id}
Route::get('/menu', [QrOrderController::class, 'show'])->name('qr.menu');

// Public menu pages
Route::get('/menu/public', [MenuController::class, 'index'])->name('menu.index');
Route::get('/menu/{id}', [MenuController::class, 'show'])->name('menu.show');

// Static pages
Route::get('/productions', fn () => view('productions'))->name('productions');
Route::get('/contact', fn () => view('contact'))->name('contact');
Route::get('/specials', [SpecialsController::class, 'index'])->name('specials');

// Optional public pages (placeholders for your CMS later)
Route::get('/gallery', fn () => view('home'));

// POS Vue SPA under `/admin/...`
//  - `/admin`                => tables
//  - `/admin/staff`         => StaffManagement.vue
//  - `/admin/reports`       => Reports.vue
//  - `/admin/menu`          => MenuManager.vue
//
// Backward compatibility: older builds used `/admin/pos/...`
Route::get('/admin/pos/{path?}', function (?string $path = null) {
    $path = trim($path ?? '', '/');
    return $path === ''
        ? redirect('/admin')
        : redirect('/admin/' . $path);
})->where('path', '.*');

Route::get('/admin/{path?}', fn () => view('pos.index'))
    ->where('path', '.*');

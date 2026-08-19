<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UploadDataController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\StoreManagerController;
use App\Http\Controllers\AreaManagerController;
use App\Http\Controllers\DebugController;
use App\Http\Controllers\ProductImportController;
use App\Http\Controllers\MasterUploadControllerFix;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\DaftarLaporanController;
use App\Http\Controllers\TargetController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect()->route('login'));
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::post('/import-product', [ProductImportController::class, 'import']);

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('dashboard')->group(function () {

        // ── Dashboard per Role ──────────────────────────────────────────
        Route::get('/store-manager', [StoreManagerController::class, 'index'])
            ->name('dashboard.store-manager');

        Route::get('/store-manager/search-snack', [StoreManagerController::class, 'searchSnackSales'])  // ✅ fix
            ->name('dashboard.store-manager.search-snack');

        Route::get('/store-manager/check-data', [StoreManagerController::class, 'checkDataExists'])
            ->name('dashboard.store-manager.check-data');


        Route::get('/area-manager', [AreaManagerController::class, 'index'])
            ->name('dashboard.area-manager');
            
        Route::get('/area-manager/search-promotion', [AreaManagerController::class, 'searchPromotion'])
                ->name('dashboard.area-manager.search-promotion');

        Route::get('/kasir', [KasirController::class, 'index'])
            ->name('dashboard.kasir');

        Route::get('/kasir/search-top-menu', [KasirController::class, 'searchTopMenu'])
            ->name('dashboard.kasir.search-top-menu');

        Route::get('/top-menu/detail', [KasirController::class, 'topMenuDetail'])
            ->name('dashboard.topmenu.detail');

        // ── Master Upload ───────────────────────────────────────────────
        Route::get('/upload-master', [MasterUploadControllerFix::class, 'index'])
            ->name('area-manager.upload-master');

        Route::post('/upload-master', [MasterUploadControllerFix::class, 'import'])
            ->name('area-manager.import-master');

        Route::post('/area-manager/store-target', [TargetController::class, 'store'])
            ->name('area-manager.store-target');

        // ── Area Manager Laporan ────────────────────────────────────────
        Route::get('/area-manager/daftar-laporan', [DaftarLaporanController::class, 'areaManager'])
            ->name('area-manager.daftar-laporan');

        Route::post('/area-manager/laporan/{id}/approve', [DaftarLaporanController::class, 'approve'])
            ->name('area-manager.approve');

        Route::post('/area-manager/laporan/{id}/reject', [DaftarLaporanController::class, 'reject'])
            ->name('area-manager.reject');

        // ── Upload Data ─────────────────────────────────────────────────
        Route::get('/upload-data', [UploadDataController::class, 'index'])
            ->name('dashboard.upload-data');

        Route::post('/import-pembayaran', [UploadDataController::class, 'importPembayaran'])
            ->name('dashboard.import-pembayaran');

        Route::post('/import-penjualan', [UploadDataController::class, 'importPenjualan'])
            ->name('dashboard.import-penjualan');

        Route::get('/download-pembayaran-template', [UploadDataController::class, 'downloadPembayaranTemplate'])
            ->name('dashboard.download-pembayaran-template');

        Route::get('/download-penjualan-template', [UploadDataController::class, 'downloadPenjualanTemplate'])
            ->name('dashboard.download-penjualan-template');

        // ── Laporan ─────────────────────────────────────────────────────
        Route::get('/daftar-laporan', [DaftarLaporanController::class, 'index'])
            ->name('dashboard.daftar-laporan');

        Route::delete('/daftar-laporan/{tanggal}', [DaftarLaporanController::class, 'destroy'])
            ->name('daftar-laporan.destroy');

        // ── API ─────────────────────────────────────────────────────────
        Route::prefix('api')->group(function () {
            Route::get('/dashboard-data', [KasirController::class, 'getDashboardData'])
                ->name('dashboard.api.data');

            Route::get('/check-data', [KasirController::class, 'checkDataExists'])
                ->name('dashboard.api.check-data');
        });

        // ── Debug  ──────────────────────────────
        Route::prefix('debug')->group(function () {
            Route::get('/test', [DebugController::class, 'testImport'])
                ->name('debug.test');

            Route::get('/upload-data', [DebugController::class, 'testUploadData'])
                ->name('debug.upload-data');
        });
    });

    // ── Laporan (di luar prefix dashboard) ─────────────────────────────
    Route::get('/laporan/{date}', [LaporanController::class, 'show'])
        ->name('laporan.show');

    Route::get('/laporan/generate/{date}', [LaporanController::class, 'generate']);
    Route::get('/generate-laporan/{date}', [LaporanController::class, 'generate']);
});
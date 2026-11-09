<?php   

use App\Http\Controllers\AdminAkunController;
use App\Http\Controllers\AdminMejaController;
use App\Http\Controllers\AdminMenuController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\KokiController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

// ======================================================
// PELANGGAN (publik, tanpa login)
// ======================================================
Route::get('/', fn () => redirect()->route('order.index', ['id_meja' => 1]));
Route::get('/order/{id_meja}', [OrderController::class, 'index'])->name('order.index');
Route::get('/order/{id_meja}/menu', [OrderController::class, 'menu'])->name('order.menu');
Route::get('/order/{id_meja}/checkout', [OrderController::class, 'checkoutPage'])->name('order.checkoutPage');
Route::post('/order/checkout', [OrderController::class, 'store'])->name('order.checkout');
Route::get('/status/{kode}', [OrderController::class, 'status'])->name('order.status');
Route::post('/status/{kode}/bukti', [OrderController::class, 'uploadBukti'])
    ->middleware('throttle:10,1')
    ->name('order.bukti');

// ======================================================
// LOGIN & LOGOUT
// ======================================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ======================================================
// ADMIN
// ======================================================
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');
    Route::get('/admin/menu/export', [AdminMenuController::class, 'export'])->name('admin.menu.export');
    Route::patch('/admin/menu/{menu}/stok', [AdminMenuController::class, 'stok'])->name('admin.menu.stok');
    Route::resource('admin/menu', AdminMenuController::class)
        ->except('show')
        ->names('admin.menu');
    Route::get('/admin/menu/export', [AdminMenuController::class, 'export'])->name('admin.menu.export');
    Route::patch('/admin/menu/{menu}/stok', [AdminMenuController::class, 'stok'])->name('admin.menu.stok');
    Route::get('/admin/laporan', [LaporanController::class, 'index'])->name('admin.laporan');
    Route::get('/admin/laporan/export', [LaporanController::class, 'export'])->name('admin.laporan.export');
    Route::resource('admin/akun', AdminAkunController::class)->except('show')->names('admin.akun');
    Route::resource('admin/meja', AdminMejaController::class)->except('show')->names('admin.meja');
    Route::get('/admin/laporan/cetak', [LaporanController::class, 'cetak'])->name('admin.laporan.cetak');
});

// ======================================================
// KASIR
// ======================================================
Route::middleware(['auth', 'role:kasir,admin'])->group(function () {
    Route::get('/kasir/pesanan', [KasirController::class, 'index'])->name('kasir.index');
    Route::get('/kasir/data', [KasirController::class, 'data'])->name('kasir.data');
    Route::post('/kasir/verifikasi/{id_pesanan}', [KasirController::class, 'verifikasi'])->name('kasir.verifikasi');
    Route::get('/kasir/bukti/{id_pesanan}', [KasirController::class, 'bukti'])->name('kasir.bukti');
    Route::get('/kasir/struk/{id_pesanan}', [KasirController::class, 'struk'])->name('kasir.struk');
});

// ======================================================
// DAPUR / KDS (admin juga boleh)
// ======================================================
Route::middleware(['auth', 'role:koki,admin'])->group(function () {
    Route::get('/koki/dapur', [KokiController::class, 'index'])->name('koki.index');
    Route::get('/koki/data', [KokiController::class, 'data'])->name('koki.data');
    Route::patch('/koki/update-status/{id_pesanan}', [KokiController::class, 'updateStatus'])->name('koki.updateStatus');
});
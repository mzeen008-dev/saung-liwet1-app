<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\PesananController;
use Illuminate\Support\Facades\Route;

// Publik (dibatasi lajunya supaya tidak bisa dibanjiri permintaan)
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::get('/menu', [MenuController::class, 'index'])->middleware('throttle:120,1');
Route::get('/menu/{menu}', [MenuController::class, 'show'])->middleware('throttle:120,1');
Route::post('/pesanan', [PesananController::class, 'store'])->middleware('throttle:20,1');
Route::get('/pesanan/kode/{kode}', [PesananController::class, 'showByKode'])->middleware('throttle:150,1');

// Harus membawa token
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('role:kasir,admin')->group(function () {
        Route::get('/pesanan', [PesananController::class, 'index']);
        Route::patch('/pesanan/{pesanan}/verifikasi', [PesananController::class, 'verifikasi']);
    });

    Route::middleware('role:koki,admin')->group(function () {
        Route::patch('/pesanan/{pesanan}/status', [PesananController::class, 'updateStatus']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::post('/menu', [MenuController::class, 'store']);
        Route::put('/menu/{menu}', [MenuController::class, 'update']);
        Route::delete('/menu/{menu}', [MenuController::class, 'destroy']);
    });
});

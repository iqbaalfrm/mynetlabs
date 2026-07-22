<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\KuisController;
use App\Http\Controllers\Api\MateriController;
use App\Http\Controllers\Api\SiswaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Endpoint Publik (Bisa diakses tanpa token)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']); // throttle sementara dinonaktifkan utk testing

// Endpoint Terproteksi (Wajib membawa Bearer Token Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::get('/user-profile', function (Request $request) {
        return $request->user();
    });

    // Materi & Pertemuan
    Route::get('/pertemuan', [MateriController::class, 'index']);
    Route::get('/pertemuan/{id}', [MateriController::class, 'show']);
    Route::post('/pertemuan/{pertemuanId}/selesai', [MateriController::class, 'tandaiPertemuanSelesai']);

    // Kuis
    Route::get('/pertemuan/{id}/kuis', [KuisController::class, 'getSoalKuis']);
    Route::post('/kuis/submit', [KuisController::class, 'submitKuis']);
    Route::get('/kuis/riwayat', [KuisController::class, 'riwayatKuis']);

    // Chat AI Tutor
    Route::get('/chat/riwayat', [ChatController::class, 'riwayat']);
    Route::post('/chat', [ChatController::class, 'kirimPesan']);
    Route::post('/chat/audio', [ChatController::class, 'kirimPesanAudio']);

    // Statistik Siswa
    Route::get('/siswa/statistik', [SiswaController::class, 'statistik']);
    Route::get('/siswa/pertemuan-aktif', [SiswaController::class, 'pertemuanAktif']);
    Route::post('/siswa/foto-profil', [SiswaController::class, 'updateFotoProfil']);
});

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MateriController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PengaturanController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\TopikController;
use App\Http\Controllers\Admin\PdfController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\SiswaController;

// Admin Auth Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Protected admin routes
    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Materi CRUD + Detail
        Route::resource('materi', MateriController::class)->except(['show']);
        Route::get('/materi/{id}', [MateriController::class, 'show'])->name('materi.show');

        // Topik Materi (nested di pertemuan)
        Route::post('/materi/{pertemuan_id}/topik', [TopikController::class, 'store'])->name('topik.store');
        Route::get('/topik/{id}/edit', [TopikController::class, 'edit'])->name('topik.edit');
        Route::put('/topik/{id}', [TopikController::class, 'update'])->name('topik.update');
        Route::delete('/topik/{id}', [TopikController::class, 'destroy'])->name('topik.destroy');

        // Modul PDF
        Route::post('/materi/{pertemuan_id}/pdf', [PdfController::class, 'upload'])->name('pdf.upload');
        Route::post('/pdf/{id}/reindex', [PdfController::class, 'reindex'])->name('pdf.reindex');
        Route::delete('/pdf/{id}', [PdfController::class, 'destroy'])->name('pdf.destroy');

        // Soal Kuis
        Route::post('/materi/{pertemuan_id}/quiz', [QuizController::class, 'store'])->name('quiz.store');
        Route::get('/quiz/{id}/edit', [QuizController::class, 'edit'])->name('quiz.edit');
        Route::put('/quiz/{id}', [QuizController::class, 'update'])->name('quiz.update');
        Route::delete('/quiz/{id}', [QuizController::class, 'destroy'])->name('quiz.destroy');
        Route::post('/materi/{pertemuan_id}/quiz/generate', [QuizController::class, 'generateByAI'])->name('quiz.generate');

        // Kelas CRUD
        Route::get('/kelas', [KelasController::class, 'index'])->name('kelas.index');
        Route::post('/kelas', [KelasController::class, 'store'])->name('kelas.store');
        Route::put('/kelas/{id}', [KelasController::class, 'update'])->name('kelas.update');
        Route::delete('/kelas/{id}', [KelasController::class, 'destroy'])->name('kelas.destroy');

        // Users
        Route::resource('users', UserController::class);

        // Siswa Management
        Route::resource('siswa', SiswaController::class);
        Route::patch('siswa/{id}/toggle-status', [SiswaController::class, 'toggleStatus'])->name('siswa.toggle-status');

        // Chat AI Monitoring
        Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
        Route::delete('/chat/{id}', [ChatController::class, 'destroy'])->name('chat.destroy');
        Route::delete('/chat/siswa/{siswaId}', [ChatController::class, 'destroyBySiswa'])->name('chat.destroyBySiswa');

        // Pengaturan
        Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
        Route::put('/pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');
    });
});

Route::get('/', function () {
    return redirect()->route('admin.login');
});
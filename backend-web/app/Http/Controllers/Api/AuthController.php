<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\ChangePasswordRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;

class AuthController extends Controller
{
    // 1. API REGISTRASI (Untuk input data siswa baru awal)
    public function register(RegisterRequest $request)
    {
        try {
            $user = User::create([
                'username' => $request->username,
                'password' => Hash::make($request->password), // Enkripsi password bcrypt
                'nama' => $request->nama,
                'role' => $request->role,
                'kelas' => $request->kelas,
            ]);

            // Buat token akses untuk user baru
            $token = $user->createToken('netlabs_token')->plainTextToken;

            return response()->json([
                'message' => 'Registrasi akun berhasil!',
                'user' => new UserResource($user),
                'token' => $token
            ], 201);
        } catch (Exception $e) {
            Log::error('Error saat registrasi user: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal mendaftarkan akun baru. Silakan coba kembali.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    // 2. API LOGIN (Dipanggil oleh aplikasi Flutter)
    public function login(LoginRequest $request)
    {
        try {
            // Cari user berdasarkan username (NIS)
            $user = User::where('username', $request->username)->first();

            // Validasi keberadaan user dan kecocokan password hash
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Nomor Induk (NIS) atau Kata Sandi salah.'
                ], 401);
            }

            // Validasi status akun (aktif/nonaktif)
            if ($user->status === 'nonaktif') {
                return response()->json([
                    'message' => 'Akun Anda telah dinonaktifkan oleh administrator. Silakan hubungi guru Anda.'
                ], 403);
            }

            // Hapus semua token lama (Single Active Session / Pembatasan Multi-Device)
            $user->tokens()->delete();
 
            // Buat token Sanctum baru
            $token = $user->createToken('netlabs_token')->plainTextToken;

            return response()->json([
                'message' => 'Login Berhasil!',
                'user' => new UserResource($user),
                'token' => $token
            ], 200);
        } catch (Exception $e) {
            Log::error('Error saat login user: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal melakukan login. Terjadi kesalahan pada server.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    // 3. API GANTI PASSWORD
    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $user = $request->user();

            if (!Hash::check($request->password_lama, $user->password)) {
                return response()->json([
                    'message' => 'Password lama tidak sesuai.'
                ], 401);
            }

            $user->password = Hash::make($request->password_baru);
            $user->password_set_at = now();
            $user->save();

            return response()->json([
                'message' => 'Password berhasil diubah.',
                'password_is_default' => false,
            ], 200);
        } catch (Exception $e) {
            Log::error('Error saat mengganti password: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal mengubah password. Silakan coba lagi.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    // 4. API LOGOUT (Menghapus Token)
    public function logout(Request $request)
    {
        try {
            // Hapus token yang sedang digunakan saat ini
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Berhasil keluar, token telah dihapus.'
            ], 200);
        } catch (Exception $e) {
            Log::error('Error saat logout: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal keluar. Silakan coba kembali.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
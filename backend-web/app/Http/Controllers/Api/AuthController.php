<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // 1. API REGISTRASI (Untuk input data siswa baru awal)
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:50|unique:users', // NIS Siswa
            'password' => 'required|string|min:6',
            'nama' => 'required|string|max:100',
            'role' => 'required|in:guru,siswa',
            'kelas' => 'required_if:role,siswa|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

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
            'user' => $user,
            'token' => $token
        ], 201);
    }

    // 2. API LOGIN (Dipanggil oleh aplikasi Flutter)
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string', // NIS
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Cari user berdasarkan username (NIS)
        $user = User::where('username', $request->username)->first();

        // Validasi keberadaan user dan kecocokan password hash
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Nomor Induk (NIS) atau Kata Sandi salah.'
            ], 401);
        }

        // Buat token Sanctum baru
        $token = $user->createToken('netlabs_token')->plainTextToken;

        return response()->json([
            'message' => 'Login Berhasil!',
            'user' => [
                'nama' => $user->nama,
                'username' => $user->username,
                'role' => $user->role,
                'kelas' => $user->kelas,
                'password_is_default' => is_null($user->password_set_at),
            ],
            'token' => $token
        ], 200);
    }

    // 3. API GANTI PASSWORD
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password_lama' => 'required|string',
            'password_baru' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

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
    }

    // 4. API LOGOUT (Menghapus Token)
    public function logout(Request $request)
    {
        // Hapus token yang sedang digunakan saat ini
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Berhasil keluar, token telah dihapus.'
        ], 200);
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Menampilkan halaman formulir login admin/guru
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    // Memproses permintaan masuk (login) admin/guru
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            // Validasi keaktifan status akun
            if ($user->status === 'nonaktif') {
                Auth::logout();
                return back()->withErrors(['username' => 'Akun Anda telah dinonaktifkan oleh administrator.']);
            }
            // Validasi peran (role) pengguna
            if (!in_array($user->role, ['guru', 'admin'])) {
                Auth::logout();
                return back()->withErrors(['username' => 'Akses hanya untuk guru/admin.']);
            }
            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors(['username' => 'NIS atau password salah.']);
    }

    // Memproses permintaan keluar (logout) sesi admin/guru
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
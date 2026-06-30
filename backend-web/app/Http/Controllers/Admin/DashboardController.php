<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TopikMateri;
use App\Models\Pertemuan;
use App\Models\SoalKuis;
use App\Models\ProgressSiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalSiswa = User::where('role', 'siswa')->count();
        $totalGuru = User::where('role', 'guru')->count();
        $totalMateri = TopikMateri::count();
        $totalPertemuan = Pertemuan::count();
        $totalSoal = SoalKuis::count();

        // Recent pertemuan
        $pertemuanList = Pertemuan::orderBy('nomor_urut')->get();

        // Top 5 siswa teratas by progress
        $topSiswa = User::where('role', 'siswa')
            ->withCount(['progressSiswa as completed_count' => function ($q) {
                $q->where('is_completed', true);
            }])
            ->orderByDesc('completed_count')
            ->limit(5)
            ->get();

        // 5 pertemuan terbaru
        $statistikPertemuan = Pertemuan::withCount(['topikMateris', 'soalKuis'])->orderByDesc('nomor_urut')->limit(5)->get();

        return view('admin.dashboard', compact(
            'totalSiswa', 'totalGuru', 'totalMateri',
            'totalPertemuan', 'totalSoal',
            'pertemuanList', 'topSiswa', 'statistikPertemuan'
        ));
    }
}

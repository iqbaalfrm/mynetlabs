<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HasilKuis;
use App\Models\ProgressSiswa;
use App\Models\Pertemuan;
use Illuminate\Http\Request;

class SiswaController extends Controller
{
    // GET /api/siswa/statistik
    // Ambil statistik belajar siswa: profil, progress pertemuan, rata-rata nilai, dll.
    public function statistik(Request $request)
    {
        $siswa = $request->user();

        $totalPertemuan = Pertemuan::count();

        // Hitung jumlah topik yang sudah diselesaikan siswa
        $topikSelesai = ProgressSiswa::where('siswa_id', $siswa->id)
            ->where('is_completed', true)
            ->count();

        // Total topik pada seluruh pertemuan
        $totalTopik = \App\Models\TopikMateri::count();

        // Hitung pertemuan yang dianggap selesai (semua topiknya selesai)
        $pertemuanSelesai = 0;
        $pertemuans = Pertemuan::with('topikMateris')->get();
        foreach ($pertemuans as $p) {
            if ($p->topikMateris->isEmpty()) {
                continue;
            }
            $topikIds = $p->topikMateris->pluck('id');
            $selesai = ProgressSiswa::where('siswa_id', $siswa->id)
                ->whereIn('topik_id', $topikIds)
                ->where('is_completed', true)
                ->count();
            if ($selesai == $topikIds->count()) {
                $pertemuanSelesai++;
            }
        }

        // Rata-rata nilai kuis
        $rataRata = HasilKuis::where('siswa_id', $siswa->id)
            ->avg('nilai');
        $rataRata = $rataRata ? round((float) $rataRata, 2) : 0;

        // Total chat ke AI
        $totalChat = \App\Models\ChatHistory::where('siswa_id', $siswa->id)->count();

        return response()->json([
            'message' => 'Statistik siswa berhasil dimuat.',
            'data' => [
                'profil' => [
                    'nis' => $siswa->username,
                    'nama' => $siswa->nama,
                    'kelas' => $siswa->kelas,
                    'role' => $siswa->role,
                    'foto_profil_url' => $siswa->foto_profil_url,
                ],
                'statistik' => [
                    'total_pertemuan_selesai' => $pertemuanSelesai,
                    'total_pertemuan' => $totalPertemuan,
                    'total_topik_selesai' => $topikSelesai,
                    'total_topik' => $totalTopik,
                    'rata_rata_nilai' => $rataRata,
                    'total_chat_ai' => $totalChat,
                ],
            ],
        ], 200);
    }

    // GET /api/siswa/pertemuan-aktif
    // Ambil daftar pertemuan yang sedang berjalan (progress > 0 dan < 1) untuk Home.
    public function pertemuanAktif(Request $request)
    {
        $siswa = $request->user();

        $pertemuans = Pertemuan::with('topikMateris')
            ->orderBy('semester')
            ->orderBy('nomor_urut')
            ->get();

        $aktif = [];
        foreach ($pertemuans as $p) {
            if ($p->topikMateris->isEmpty()) {
                continue;
            }
            $topikIds = $p->topikMateris->pluck('id');
            $selesai = ProgressSiswa::where('siswa_id', $siswa->id)
                ->whereIn('topik_id', $topikIds)
                ->where('is_completed', true)
                ->count();
            $total = $topikIds->count();
            $progress = $total > 0 ? round($selesai / $total, 2) : 0;

            // Hanya tampilkan yang sedang berjalan (0 < progress < 1)
            if ($progress > 0 && $progress < 1) {
                $aktif[] = [
                    'id' => $p->id,
                    'nomor' => $p->nomor_urut,
                    'judul' => $p->judul,
                    'topik' => "$total Topik",
                    'progress' => $progress,
                ];
            }
        }

        return response()->json([
            'message' => 'Pertemuan aktif berhasil dimuat.',
            'data' => $aktif,
        ], 200);
    }

    // POST /api/siswa/foto-profil
    // Mengunggah dan memperbarui foto profil siswa
    public function updateFotoProfil(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $siswa = $request->user();

        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($siswa->foto_profil && \Illuminate\Support\Facades\Storage::disk('public')->exists($siswa->foto_profil)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($siswa->foto_profil);
            }

            // Simpan foto baru
            $path = $request->file('foto')->store('avatars', 'public');
            $siswa->foto_profil = $path;
            $siswa->save();

            return response()->json([
                'success' => true,
                'message' => 'Foto profil berhasil diperbarui.',
                'foto_profil_url' => $siswa->foto_profil_url,
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal mengunggah foto profil.',
        ], 400);
    }
}

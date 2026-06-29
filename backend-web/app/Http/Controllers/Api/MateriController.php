<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pertemuan;
use App\Models\ProgressSiswa;
use App\Models\TopikMateri;
use Illuminate\Http\Request;

class MateriController extends Controller
{
    // GET /api/pertemuan
    // Ambil semua pertemuan, dikelompokkan per semester.
    // Untuk siswa, sertakan progress belajar tiap pertemuan.
    public function index(Request $request)
    {
        $siswa = $request->user();

        $pertemuans = Pertemuan::orderBy('semester')
            ->orderBy('nomor_urut')
            ->get();

        $data = $pertemuans->map(function ($p) use ($siswa) {
            // Hitung total topik pada pertemuan ini
            $totalTopik = $p->topikMateris()->count();

            // Hitung topik yang sudah diselesaikan siswa (jika siswa login)
            $topikSelesai = 0;
            if ($siswa && $siswa->role === 'siswa') {
                $topikSelesai = ProgressSiswa::where('siswa_id', $siswa->id)
                    ->whereIn('topik_id', $p->topikMateris()->pluck('id'))
                    ->where('is_completed', true)
                    ->count();
            }

            $progress = $totalTopik > 0 ? round($topikSelesai / $totalTopik, 2) : 0;

            // Status indexing dari modul_pdf terbaru (untuk AI badge di mobile)
            $latestModul = $p->modulPdfs()->latest()->first();
            $statusIndexing = $latestModul?->status_indexing ?? 'pending';

            return [
                'id' => $p->id,
                'nomor' => $p->nomor_urut,
                'judul' => $p->judul,
                'deskripsi' => $p->deskripsi,
                'semester' => $p->semester,
                'warna_tema' => $p->warna_tema,
                'topik_count' => $totalTopik,
                'progress' => $progress,
                'is_completed' => $progress >= 1.0,
                'status_indexing' => $statusIndexing,
            ];
        });

        // Kelompokkan berdasarkan semester untuk memudahkan tab di mobile
        $grouped = [
            '1' => $data->where('semester', '1')->values(),
            '2' => $data->where('semester', '2')->values(),
        ];

        return response()->json([
            'message' => 'Daftar pertemuan berhasil dimuat.',
            'data' => $grouped,
        ], 200);
    }

    // GET /api/pertemuan/{id}
    // Detail satu pertemuan beserta daftar topik materinya.
    public function show(Request $request, $id)
    {
        $siswa = $request->user();

        $pertemuan = Pertemuan::with('topikMateris')->find($id);

        if (!$pertemuan) {
            return response()->json([
                'message' => 'Pertemuan tidak ditemukan.',
            ], 404);
        }

        $topikIds = $pertemuan->topikMateris->pluck('id');

        // Ambil daftar topik yang sudah diselesaikan siswa
        $topikSelesaiIds = [];
        if ($siswa && $siswa->role === 'siswa') {
            $topikSelesaiIds = ProgressSiswa::where('siswa_id', $siswa->id)
                ->whereIn('topik_id', $topikIds)
                ->where('is_completed', true)
                ->pluck('topik_id')
                ->toArray();
        }

        $topik = $pertemuan->topikMateris->map(function ($t) use ($topikSelesaiIds) {
            return [
                'id' => $t->id,
                'judul' => $t->judul,
                'isi' => $t->isi_materi,
                'is_completed' => in_array($t->id, $topikSelesaiIds),
            ];
        });

        return response()->json([
            'message' => 'Detail pertemuan berhasil dimuat.',
            'data' => [
                'id' => $pertemuan->id,
                'nomor' => $pertemuan->nomor_urut,
                'judul' => $pertemuan->judul,
                'deskripsi' => $pertemuan->deskripsi,
                'semester' => $pertemuan->semester,
                'warna_tema' => $pertemuan->warna_tema,
                'daftar_topik' => $topik,
            ],
        ], 200);
    }

    // POST /api/pertemuan/{pertemuanId}/topik/{topikId}/selesai
    // Tandai satu topik materi sebagai sudah dibaca/selesai oleh siswa.
    public function tandaiTopikSelesai(Request $request, $pertemuanId, $topikId)
    {
        $siswa = $request->user();

        if (!$siswa || $siswa->role !== 'siswa') {
            return response()->json([
                'message' => 'Hanya siswa yang dapat menandai progress belajar.',
            ], 403);
        }

        $topik = TopikMateri::where('id', $topikId)
            ->where('pertemuan_id', $pertemuanId)
            ->first();

        if (!$topik) {
            return response()->json([
                'message' => 'Topik materi tidak ditemukan.',
            ], 404);
        }

        // Gunakan updateOrCreate agar idempoten (tidak duplikat)
        ProgressSiswa::updateOrCreate(
            ['siswa_id' => $siswa->id, 'topik_id' => $topikId],
            ['is_completed' => true]
        );

        return response()->json([
            'message' => 'Topik berhasil ditandai selesai.',
        ], 200);
    }
}

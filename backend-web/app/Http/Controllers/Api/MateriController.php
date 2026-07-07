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

        $topikSelesaiIds = [];
        if ($siswa && $siswa->role === 'siswa') {
            $topikSelesaiIds = ProgressSiswa::where('siswa_id', $siswa->id)
                ->where('is_completed', true)
                ->pluck('topik_id')
                ->toArray();
        }

        $pertemuans = Pertemuan::orderBy('semester')
            ->orderBy('nomor_urut')
            ->withCount('topikMateris as total_topik')
            ->with(['modulPdfs' => fn($q) => $q->latest()->limit(1)])
            ->get();

        $data = $pertemuans->map(function ($p) use ($siswa, $topikSelesaiIds) {
            $totalTopik = $p->total_topik;

            $topikSelesai = 0;
            if ($siswa && $siswa->role === 'siswa') {
                $topikSelesai = $p->topikMateris()
                    ->whereIn('id', $topikSelesaiIds)
                    ->count();
            }

            $progress = $totalTopik > 0 ? round($topikSelesai / $totalTopik, 2) : 0;

            $statusIndexing = $p->modulPdfs->first()?->status_indexing ?? 'pending';

            return [
                'id' => $p->id,
                'nomor' => $p->nomor_urut,
                'urutan' => $p->nomor_urut,
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

        return response()->json([
            'message' => 'Daftar pertemuan berhasil dimuat.',
            'data' => $data,
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
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pertemuan;
use App\Models\ProgressSiswa;
use App\Models\TopikMateri;
use App\Http\Resources\PertemuanResource;
use App\Http\Resources\TopikMateriResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class MateriController extends Controller
{
    // GET /api/pertemuan
    // Ambil semua pertemuan, dikelompokkan per semester.
    // Untuk siswa, sertakan progress belajar tiap pertemuan.
    public function index(Request $request)
    {
        try {
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

            $pertemuans->map(function ($p) use ($siswa, $topikSelesaiIds) {
                $totalTopik = $p->total_topik;

                $topikSelesai = 0;
                if ($siswa && $siswa->role === 'siswa') {
                    $topikSelesai = $p->topikMateris()
                        ->whereIn('id', $topikSelesaiIds)
                        ->count();
                }

                $p->progress = $totalTopik > 0 ? round($topikSelesai / $totalTopik, 2) : 0;
                $p->status_indexing = $p->modulPdfs->first()?->status_indexing ?? 'pending';
                return $p;
            });

            return response()->json([
                'message' => 'Daftar pertemuan berhasil dimuat.',
                'data' => PertemuanResource::collection($pertemuans),
            ], 200);
        } catch (Exception $e) {
            Log::error('Error saat memuat daftar pertemuan: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal memuat daftar pertemuan.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    // GET /api/pertemuan/{id}
    // Detail satu pertemuan beserta daftar topik materinya.
    public function show(Request $request, $id)
    {
        try {
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

            $pertemuan->topikMateris->map(function ($t) use ($topikSelesaiIds) {
                $t->is_completed = in_array($t->id, $topikSelesaiIds);
                return $t;
            });

            return response()->json([
                'message' => 'Detail pertemuan berhasil dimuat.',
                'data' => new PertemuanResource($pertemuan),
            ], 200);
        } catch (Exception $e) {
            Log::error('Error saat memuat detail pertemuan: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal memuat detail pertemuan.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    // POST /api/pertemuan/{pertemuanId}/topik/{topikId}/selesai
    // Tandai satu topik materi sebagai sudah dibaca/selesai oleh siswa.
    public function tandaiTopikSelesai(Request $request, $pertemuanId, $topikId)
    {
        try {
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

            // Gunakan updateOrCreate agar idempoten
            ProgressSiswa::updateOrCreate(
                ['siswa_id' => $siswa->id, 'topik_id' => $topikId],
                ['is_completed' => true]
            );

            return response()->json([
                'message' => 'Topik berhasil ditandai selesai.',
            ], 200);
        } catch (Exception $e) {
            Log::error('Error saat menandai topik selesai: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal memperbarui progress belajar.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pertemuan;
use App\Models\ProgressSiswa;
use App\Http\Resources\PertemuanResource;
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

            // Ambil daftar pertemuan_id yang sudah ditandai selesai oleh siswa
            $pertemuanSelesaiIds = [];
            if ($siswa && $siswa->role === 'siswa') {
                $pertemuanSelesaiIds = ProgressSiswa::where('siswa_id', $siswa->id)
                    ->whereNotNull('pertemuan_id')
                    ->where('is_completed', true)
                    ->pluck('pertemuan_id')
                    ->toArray();
            }

            $pertemuans = Pertemuan::orderBy('semester')
                ->orderBy('nomor_urut')
                ->with(['modulPdfs' => fn($q) => $q->latest()->limit(1)])
                ->get();

            $pertemuans->map(function ($p) use ($siswa, $pertemuanSelesaiIds) {
                // Progress bersifat binary: 0 (belum selesai) atau 1 (sudah selesai)
                $p->progress = in_array($p->id, $pertemuanSelesaiIds) ? 1.0 : 0.0;
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
    // Detail satu pertemuan beserta isi materi langsung.
    public function show(Request $request, $id)
    {
        try {
            $siswa = $request->user();

            $pertemuan = Pertemuan::with([
                'modulPdfs' => fn($q) => $q->latest()->limit(1)
            ])->find($id);

            if (!$pertemuan) {
                return response()->json([
                    'message' => 'Pertemuan tidak ditemukan.',
                ], 404);
            }

            // Cek apakah siswa sudah menandai pertemuan ini sebagai selesai
            $isCompleted = false;
            if ($siswa && $siswa->role === 'siswa') {
                $isCompleted = ProgressSiswa::where('siswa_id', $siswa->id)
                    ->where('pertemuan_id', $pertemuan->id)
                    ->where('is_completed', true)
                    ->exists();
            }

            $pertemuan->progress = $isCompleted ? 1.0 : 0.0;

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

    // POST /api/pertemuan/{pertemuanId}/selesai
    // Tandai satu pertemuan sebagai sudah dibaca/selesai oleh siswa.
    public function tandaiPertemuanSelesai(Request $request, $pertemuanId)
    {
        try {
            $siswa = $request->user();

            if (!$siswa || $siswa->role !== 'siswa') {
                return response()->json([
                    'message' => 'Hanya siswa yang dapat menandai progress belajar.',
                ], 403);
            }

            $pertemuan = Pertemuan::find($pertemuanId);

            if (!$pertemuan) {
                return response()->json([
                    'message' => 'Pertemuan tidak ditemukan.',
                ], 404);
            }

            // Gunakan updateOrCreate agar idempoten
            ProgressSiswa::updateOrCreate(
                ['siswa_id' => $siswa->id, 'pertemuan_id' => $pertemuanId],
                ['is_completed' => true]
            );

            return response()->json([
                'message' => 'Pertemuan berhasil ditandai selesai.',
            ], 200);
        } catch (Exception $e) {
            Log::error('Error saat menandai pertemuan selesai: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal memperbarui progress belajar.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HasilKuis;
use App\Models\Pertemuan;
use App\Models\SoalKuis;
use App\Http\Requests\Api\Kuis\SubmitKuisRequest;
use App\Http\Resources\SoalKuisResource;
use App\Http\Resources\HasilKuisResource;
use App\Services\KuisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class KuisController extends Controller
{
    protected KuisService $kuisService;

    public function __construct(KuisService $kuisService)
    {
        $this->kuisService = $kuisService;
    }

    // GET /api/pertemuan/{id}/kuis
    // Ambil daftar soal kuis untuk satu pertemuan.
    // Kunci jawaban sengaja TIDAK dikirim agar siswa tidak bisa curang.
    public function getSoalKuis($id)
    {
        try {
            $pertemuan = Pertemuan::find($id);

            if (!$pertemuan) {
                return response()->json([
                    'message' => 'Pertemuan tidak ditemukan.',
                ], 404);
            }

            $soal = SoalKuis::where('pertemuan_id', $id)
                ->orderBy('id')
                ->get();

            return response()->json([
                'message' => 'Soal kuis berhasil dimuat.',
                'data' => [
                    'pertemuan' => [
                        'id' => $pertemuan->id,
                        'nomor' => $pertemuan->nomor_urut,
                        'judul' => $pertemuan->judul,
                    ],
                    'total_soal' => $soal->count(),
                    'soal' => SoalKuisResource::collection($soal),
                ],
            ], 200);
        } catch (Exception $e) {
            Log::error('Error saat mengambil soal kuis: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal memuat soal kuis.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    // POST /api/kuis/submit
    // Submit jawaban kuis siswa, hitung nilai, simpan hasil, dan kembalikan rekomendasi.
    public function submitKuis(SubmitKuisRequest $request)
    {
        try {
            $siswa = $request->user();

            if (!$siswa || $siswa->role !== 'siswa') {
                return response()->json([
                    'message' => 'Hanya siswa yang dapat mengirim jawaban kuis.',
                ], 403);
            }

            $result = $this->kuisService->prosesSubmitKuis(
                $siswa,
                $request->pertemuan_id,
                $request->jawaban
            );

            return response()->json([
                'message' => 'Kuis berhasil disubmit!',
                'data' => [
                    'hasil_id' => $result['hasil']->id,
                    'nilai' => $result['nilai'],
                    'jumlah_benar' => $result['jumlah_benar'],
                    'total_soal' => $result['total_soal'],
                    'rekomendasi_ai' => $result['rekomendasi_ai'],
                    'pembahasan' => $result['pembahasan'],
                ],
            ], 200);
        } catch (Exception $e) {
            Log::error('Error saat submit kuis: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal mensubmit kuis. Terjadi kesalahan server.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    // GET /api/kuis/riwayat
    // Riwayat hasil kuis milik siswa yang sedang login.
    public function riwayatKuis(Request $request)
    {
        try {
            $siswa = $request->user();

            $riwayat = HasilKuis::with('pertemuan')
                ->where('siswa_id', $siswa->id)
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'message' => 'Riwayat kuis berhasil dimuat.',
                'data' => HasilKuisResource::collection($riwayat),
            ], 200);
        } catch (Exception $e) {
            Log::error('Error saat memuat riwayat kuis: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal memuat riwayat kuis.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}

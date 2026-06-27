<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HasilKuis;
use App\Models\Pertemuan;
use App\Models\SoalKuis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KuisController extends Controller
{
    // GET /api/pertemuan/{id}/kuis
    // Ambil daftar soal kuis untuk satu pertemuan.
    // Kunci jawaban sengaja TIDAK dikirim agar siswa tidak bisa curang.
    public function getSoalKuis($id)
    {
        $pertemuan = Pertemuan::find($id);

        if (!$pertemuan) {
            return response()->json([
                'message' => 'Pertemuan tidak ditemukan.',
            ], 404);
        }

        $soal = SoalKuis::where('pertemuan_id', $id)
            ->orderBy('id')
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'pertanyaan' => $s->pertanyaan,
                    'pilihan' => [
                        'A' => $s->pilihan_a,
                        'B' => $s->pilihan_b,
                        'C' => $s->pilihan_c,
                        'D' => $s->pilihan_d,
                    ],
                ];
            });

        return response()->json([
            'message' => 'Soal kuis berhasil dimuat.',
            'data' => [
                'pertemuan' => [
                    'id' => $pertemuan->id,
                    'nomor' => $pertemuan->nomor_urut,
                    'judul' => $pertemuan->judul,
                ],
                'total_soal' => $soal->count(),
                'soal' => $soal,
            ],
        ], 200);
    }

    // POST /api/kuis/submit
    // Submit jawaban kuis siswa, hitung nilai, simpan hasil, dan kembalikan rekomendasi.
    public function submitKuis(Request $request)
    {
        $siswa = $request->user();

        if (!$siswa || $siswa->role !== 'siswa') {
            return response()->json([
                'message' => 'Hanya siswa yang dapat mengirim jawaban kuis.',
            ], 403);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'pertemuan_id' => 'required|exists:pertemuan,id',
            'jawaban' => 'required|array',
            'jawaban.*.soal_id' => 'required|exists:soal_kuis,id',
            'jawaban.*.jawaban' => 'required|in:A,B,C,D',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $pertemuanId = $request->pertemuan_id;
        $totalSoal = SoalKuis::where('pertemuan_id', $pertemuanId)->count();

        $jumlahBenar = 0;
        $pembahasan = [];

        foreach ($request->jawaban as $j) {
            $soal = SoalKuis::find($j['soal_id']);
            if (!$soal || $soal->pertemuan_id != $pertemuanId) {
                continue;
            }

            $benar = ($j['jawaban'] === $soal->kunci_jawaban);
            if ($benar) {
                $jumlahBenar++;
            }

            $pembahasan[] = [
                'soal_id' => $soal->id,
                'pertanyaan' => $soal->pertanyaan,
                'jawaban_siswa' => $j['jawaban'],
                'kunci_jawaban' => $soal->kunci_jawaban,
                'is_benar' => $benar,
                'penjelasan' => $soal->penjelasan,
            ];
        }

        $nilai = $totalSoal > 0 ? round(($jumlahBenar / $totalSoal) * 100, 2) : 0;
        $rekomendasi = $this->generateRekomendasi($nilai);

        $hasil = DB::transaction(function () use ($siswa, $pertemuanId, $nilai, $jumlahBenar, $totalSoal, $rekomendasi) {
            return HasilKuis::create([
                'siswa_id' => $siswa->id,
                'pertemuan_id' => $pertemuanId,
                'nilai' => $nilai,
                'jumlah_benar' => $jumlahBenar,
                'total_soal' => $totalSoal,
                'rekomendasi_ai' => $rekomendasi,
            ]);
        });

        return response()->json([
            'message' => 'Kuis berhasil disubmit!',
            'data' => [
                'hasil_id' => $hasil->id,
                'nilai' => $nilai,
                'jumlah_benar' => $jumlahBenar,
                'total_soal' => $totalSoal,
                'rekomendasi_ai' => $rekomendasi,
                'pembahasan' => $pembahasan,
            ],
        ], 200);
    }

    // GET /api/kuis/riwayat
    // Riwayat hasil kuis milik siswa yang sedang login.
    public function riwayatKuis(Request $request)
    {
        $siswa = $request->user();

        $riwayat = HasilKuis::with('pertemuan')
            ->where('siswa_id', $siswa->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($h) {
                return [
                    'id' => $h->id,
                    'pertemuan' => $h->pertemuan ? $h->pertemuan->judul : '-',
                    'nomor_pertemuan' => $h->pertemuan ? $h->pertemuan->nomor_urut : '-',
                    'nilai' => (float) $h->nilai,
                    'jumlah_benar' => $h->jumlah_benar,
                    'total_soal' => $h->total_soal,
                    'tanggal' => $h->created_at->format('Y-m-d H:i'),
                ];
            });

        return response()->json([
            'message' => 'Riwayat kuis berhasil dimuat.',
            'data' => $riwayat,
        ], 200);
    }

    // Helper: Generate rekomendasi berbasis aturan (nanti bisa diganti AI eksternal).
    private function generateRekomendasi($nilai)
    {
        if ($nilai == 100) {
            return 'Luar biasa! Anda telah memahami seluruh konsep praktikum dengan sangat baik. Pertahankan prestasi Anda dan tetap konsisten belajar!';
        } elseif ($nilai >= 70) {
            return 'Hasil yang baik! Anda telah memahami sebagian besar konsep. Untuk memaksimalkan pemahaman, tinjau kembali modul yang belum dikuasai dan tanyakan ke AI Tutor.';
        } else {
            return 'Upaya yang bagus! Namun, Anda perlu meninjau ulang materi praktikum. Silakan berkonsultasi dengan AI Tutor untuk penjelasan lebih detail pada bagian yang masih membingungkan.';
        }
    }
}

<?php

namespace App\Services;

use App\Models\HasilKuis;
use App\Models\SoalKuis;
use Illuminate\Support\Facades\DB;

class KuisService
{
    /**
     * Submit kuis, hitung nilai, simpan ke database, dan buat pembahasan.
     */
    public function prosesSubmitKuis($siswa, int $pertemuanId, array $jawabanList): array
    {
        // Batch load semua soal untuk menghindari N+1 query
        $soalIds = collect($jawabanList)->pluck('soal_id')->unique();
        $soalMap = SoalKuis::whereIn('id', $soalIds)
            ->where('pertemuan_id', $pertemuanId)
            ->get()
            ->keyBy('id');

        $totalSoal = SoalKuis::where('pertemuan_id', $pertemuanId)->count();
        $jumlahBenar = 0;
        $pembahasan = [];

        foreach ($jawabanList as $j) {
            $soal = $soalMap->get($j['soal_id']);
            if (!$soal) {
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

        return [
            'hasil' => $hasil,
            'nilai' => $nilai,
            'jumlah_benar' => $jumlahBenar,
            'total_soal' => $totalSoal,
            'rekomendasi_ai' => $rekomendasi,
            'pembahasan' => $pembahasan,
        ];
    }

    /**
     * Membuat rekomendasi berdasarkan nilai kuis.
     */
    public function generateRekomendasi($nilai): string
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

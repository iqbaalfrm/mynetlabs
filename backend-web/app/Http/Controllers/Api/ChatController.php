<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatHistory;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    // GET /api/chat/riwayat
    // Ambil riwayat chat AI tutor milik siswa yang sedang login (semua pertemuan).
    public function riwayat(Request $request)
    {
        $siswa = $request->user();

        $riwayat = ChatHistory::where('siswa_id', $siswa->id)
            ->orderBy('created_at')
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'sender' => $c->sender,
                    'pesan' => $c->pesan,
                    'sumber' => $c->sumber_referensi,
                    'waktu' => $c->created_at->format('Y-m-d H:i'),
                ];
            });

        return response()->json([
            'message' => 'Riwayat chat berhasil dimuat.',
            'data' => $riwayat,
        ], 200);
    }

    // POST /api/chat
    // Kirim pesan siswa, dapatkan balasan AI Tutor.
    // Untuk saat ini AI Tutor memakai balasan berbasis aturan sederhana
    // (nanti dapat diintegrasikan dengan model AI / RAG endpoint eksternal).
    public function kirimPesan(Request $request)
    {
        $siswa = $request->user();

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'pertemuan_id' => 'nullable|exists:pertemuan,id',
            'pesan' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 1. Simpan pesan siswa
        ChatHistory::create([
            'siswa_id' => $siswa->id,
            'pertemuan_id' => $request->pertemuan_id,
            'sender' => 'siswa',
            'pesan' => $request->pesan,
            'sumber_referensi' => null,
        ]);

        // 2. Generate balasan AI (menggunakan Flask AI Engine / RAG)
        $sumber = 'Netlabs AI Tutor';
        $balasan = 'Maaf, terjadi kesalahan saat menghubungi AI Tutor.';

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(60)->post('http://127.0.0.1:5050/chat', [
                'pertemuan_id' => $request->pertemuan_id,
                'message' => $request->pesan,
            ]);

            if ($response->successful() && $response->json('success')) {
                $balasan = $response->json('answer');
                // Bisa juga menambahkan info chunk_used jika ingin dikembalikan ke mobile
            } else {
                $balasan = $response->json('answer') ?? 'Maaf, gagal mendapatkan jawaban dari AI (Error API).';
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('AI Chat Error: ' . $e->getMessage());
            $balasan = 'Maaf, koneksi ke mesin AI sedang bermasalah. Coba lagi nanti.';
        }

        // 3. Simpan balasan AI
        ChatHistory::create([
            'siswa_id' => $siswa->id,
            'pertemuan_id' => $request->pertemuan_id,
            'sender' => 'ai',
            'pesan' => $balasan,
            'sumber_referensi' => $sumber,
        ]);

        return response()->json([
            'message' => 'Pesan berhasil diproses.',
            'data' => [
                'sender' => 'ai',
                'pesan' => $balasan,
                'sumber' => $sumber,
                'waktu' => now()->format('Y-m-d H:i'),
            ],
        ], 200);
    }

    // POST /api/chat/audio
    // Kirim pesan suara (audio) siswa, transkripsikan menjadi teks,
    // lalu proses seperti chat teks biasa via AI Tutor.
    public function kirimPesanAudio(Request $request)
    {
        $siswa = $request->user();

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'pertemuan_id' => 'nullable|exists:pertemuan,id',
            'audio' => 'required|file|mimes:wav,mp3,m4a,ogg,webm,aac|max:10240', // Maks 10MB
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $audioFile = $request->file('audio');

        // 1. Transkripsikan audio menjadi teks via Flask AI Engine
        $pesanTeks = null;
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(30)
                ->attach(
                    'audio',
                    file_get_contents($audioFile->getRealPath()),
                    $audioFile->getClientOriginalName()
                )
                ->post('http://127.0.0.1:5050/transcribe');

            if ($response->successful() && $response->json('success')) {
                $pesanTeks = $response->json('text');
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Audio Transcription Error: ' . $e->getMessage());
        }

        // Fallback jika transkripsi gagal
        if (empty($pesanTeks)) {
            return response()->json([
                'message' => 'Gagal mentranskripsi audio. Silakan coba lagi atau gunakan teks.',
                'data' => null,
            ], 422);
        }

        // 2. Simpan pesan siswa (hasil transkripsi)
        ChatHistory::create([
            'siswa_id' => $siswa->id,
            'pertemuan_id' => $request->pertemuan_id,
            'sender' => 'siswa',
            'pesan' => $pesanTeks,
            'sumber_referensi' => null,
        ]);

        // 3. Kirim ke AI Tutor (sama seperti chat teks)
        $sumber = 'Netlabs AI Tutor';
        $balasan = 'Maaf, terjadi kesalahan saat menghubungi AI Tutor.';

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(60)->post('http://127.0.0.1:5050/chat', [
                'pertemuan_id' => $request->pertemuan_id,
                'message' => $pesanTeks,
            ]);

            if ($response->successful() && $response->json('success')) {
                $balasan = $response->json('answer');
            } else {
                $balasan = $response->json('answer') ?? 'Maaf, gagal mendapatkan jawaban dari AI (Error API).';
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('AI Chat Error (Audio): ' . $e->getMessage());
            $balasan = 'Maaf, koneksi ke mesin AI sedang bermasalah. Coba lagi nanti.';
        }

        // 4. Simpan balasan AI
        ChatHistory::create([
            'siswa_id' => $siswa->id,
            'pertemuan_id' => $request->pertemuan_id,
            'sender' => 'ai',
            'pesan' => $balasan,
            'sumber_referensi' => $sumber,
        ]);

        return response()->json([
            'message' => 'Pesan audio berhasil diproses.',
            'data' => [
                'transkripsi' => $pesanTeks,
                'sender' => 'ai',
                'pesan' => $balasan,
                'sumber' => $sumber,
                'waktu' => now()->format('Y-m-d H:i'),
            ],
        ], 200);
    }
}

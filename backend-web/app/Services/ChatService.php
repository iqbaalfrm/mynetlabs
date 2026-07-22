<?php

namespace App\Services;

use App\Models\ChatHistory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ChatService
{
    protected string $aiUrl;

    public function __construct()
    {
        $this->aiUrl = config('services.ai_service.url', 'http://127.0.0.1:5050');
    }

    /**
     * Kirim pesan teks siswa ke AI Tutor dan simpan ke database.
     */
    public function kirimPesanTeks($siswa, ?int $pertemuanId, string $pesan): array
    {
        // 1. Simpan pesan siswa
        ChatHistory::create([
            'siswa_id' => $siswa->id,
            'pertemuan_id' => $pertemuanId,
            'sender' => 'siswa',
            'pesan' => $pesan,
            'sumber_referensi' => null,
        ]);

        // 2. Generate balasan AI
        $sumber = 'Netlabs AI Tutor';
        $balasan = 'Maaf, terjadi kesalahan saat menghubungi AI Tutor.';
        $sources = [];
        $chunksUsed = 0;
        $retrievalMethod = 'hybrid_bm25_dense_rrf_reranker';
        $retrievalDetails = [];

        try {
            $response = Http::timeout(60)->post("{$this->aiUrl}/chat", [
                'pertemuan_id' => $pertemuanId,
                'message' => $pesan,
            ]);

            if ($response->successful()) {
                $balasan = $response->json('answer') ?? 'Maaf, gagal mendapatkan jawaban dari AI (Error API).';
                $sources = $response->json('sources') ?? [];
                $chunksUsed = $response->json('chunks_used') ?? 0;
                $retrievalMethod = $response->json('retrieval_method') ?? $retrievalMethod;
                $retrievalDetails = $response->json('retrieval_details') ?? [];
                
                if (!empty($sources)) {
                    $sumber = implode(', ', $sources);
                }
            } else {
                $balasan = $response->json('answer') ?? 'Maaf, gagal mendapatkan jawaban dari AI (Error API).';
            }
        } catch (Exception $e) {
            Log::error('AI Chat Error: ' . $e->getMessage());
            $balasan = 'Maaf, koneksi ke mesin AI sedang bermasalah. Coba lagi nanti.';
        }

        // 3. Simpan balasan AI
        $chatAi = ChatHistory::create([
            'siswa_id' => $siswa->id,
            'pertemuan_id' => $pertemuanId,
            'sender' => 'ai',
            'pesan' => $balasan,
            'sumber_referensi' => $sumber,
        ]);

        return [
            'sender' => 'ai',
            'pesan' => $balasan,
            'sumber' => $sumber,
            'waktu' => $chatAi->created_at->format('Y-m-d H:i'),
            'sources' => $sources,
            'chunks_used' => $chunksUsed,
            'retrieval_method' => $retrievalMethod,
            'retrieval_details' => $retrievalDetails,
        ];
    }

    /**
     * Transkripsi audio menjadi teks menggunakan Flask AI.
     */
    public function transkripsikanAudio($audioFile): ?string
    {
        try {
            $response = Http::timeout(30)
                ->attach(
                    'audio',
                    file_get_contents($audioFile->getRealPath()),
                    $audioFile->getClientOriginalName()
                )
                ->post("{$this->aiUrl}/transcribe");

            if ($response->successful() && $response->json('success')) {
                return $response->json('text');
            }
        } catch (Exception $e) {
            Log::error('Audio Transcription Error: ' . $e->getMessage());
        }

        return null;
    }
}

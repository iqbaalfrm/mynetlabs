<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatHistory;
use App\Http\Requests\Api\Chat\KirimPesanRequest;
use App\Http\Requests\Api\Chat\KirimPesanAudioRequest;
use App\Http\Resources\ChatHistoryResource;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class ChatController extends Controller
{
    protected ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    // GET /api/chat/riwayat
    // Ambil riwayat chat AI tutor milik siswa yang sedang login (semua pertemuan).
    public function riwayat(Request $request)
    {
        try {
            $siswa = $request->user();

            $riwayat = ChatHistory::where('siswa_id', $siswa->id)
                ->orderBy('created_at')
                ->get();

            return response()->json([
                'message' => 'Riwayat chat berhasil dimuat.',
                'data' => ChatHistoryResource::collection($riwayat),
            ], 200);
        } catch (Exception $e) {
            Log::error('Error saat memuat riwayat chat: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal memuat riwayat chat.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    // POST /api/chat
    // Kirim pesan siswa, dapatkan balasan AI Tutor.
    public function kirimPesan(KirimPesanRequest $request)
    {
        try {
            $siswa = $request->user();
            $result = $this->chatService->kirimPesanTeks(
                $siswa,
                $request->pertemuan_id,
                $request->pesan
            );

            return response()->json([
                'message' => 'Pesan berhasil diproses.',
                'data' => $result,
            ], 200);
        } catch (Exception $e) {
            Log::error('Error saat mengirim pesan chat: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan internal saat memproses pesan.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    // POST /api/chat/audio
    // Kirim pesan suara (audio) siswa, transkripsikan menjadi teks,
    // lalu proses seperti chat teks biasa via AI Tutor.
    public function kirimPesanAudio(KirimPesanAudioRequest $request)
    {
        try {
            $siswa = $request->user();
            $audioFile = $request->file('audio');

            // 1. Transkripsikan audio menjadi teks via AI
            $pesanTeks = $this->chatService->transkripsikanAudio($audioFile);

            // Fallback jika transkripsi gagal
            if (empty($pesanTeks)) {
                return response()->json([
                    'message' => 'Gagal mentranskripsi audio. Silakan coba lagi atau gunakan teks.',
                    'data' => null,
                ], 422);
            }

            // 2. Kirim pesan hasil transkripsi ke AI Tutor
            $result = $this->chatService->kirimPesanTeks(
                $siswa,
                $request->pertemuan_id,
                $pesanTeks
            );

            return response()->json([
                'message' => 'Pesan audio berhasil diproses.',
                'data' => array_merge(['transkripsi' => $pesanTeks], $result),
            ], 200);
        } catch (Exception $e) {
            Log::error('Error saat memproses chat audio: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan internal saat memproses pesan audio.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}

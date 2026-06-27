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

        // 2. Generate balasan AI (placeholder berbasis aturan)
        $balasan = $this->generateAiReply($request->pesan);
        $sumber = 'Netlabs AI Tutor';

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

    // Helper: Balasan AI sederhana berbasis kata kunci.
    // Catatan: Ganti fungsi ini dengan pemanggilan service AI/RAG sungguhan nantinya.
    private function generateAiReply($pesan)
    {
        $pesanLower = strtolower($pesan);

        if (str_contains($pesanLower, 'vlsm') || str_contains($pesanLower, 'subnetting')) {
            return 'Untuk menghitung subnetting menggunakan metode VLSM, urutkan kebutuhan host dari yang terbesar ke terkecil. Alokasikan blok IP mulai dari prefix yang paling besar (mis. /26 untuk 64 IP) hingga prefix terkecil. Hal ini menjaga efisiensi alokasi IP Address.';
        }

        if (str_contains($pesanLower, 'cidr') || str_contains($pesanLower, 'classless')) {
            return 'CIDR (Classless Inter-Domain Routing) adalah metode pengalamatan IP tanpa kelas. CIDR menggunakan prefix (mis. /24, /25) untuk menentukan batas network dan host secara fleksibel dibanding sistem classful klasik.';
        }

        if (str_contains($pesanLower, 'ping') || str_contains($pesanLower, 'rto')) {
            return 'Jika ping ke gateway menghasilkan "Request Timed Out" (RTO), periksa: (1) kabel jaringan tersambung, (2) IP perangkat satu subnet dengan gateway, (3) firewall tidak memblokir ICMP, dan (4) gateway dalam keadaan aktif.';
        }

        if (str_contains($pesanLower, 'dhcp')) {
            return 'DHCP (Dynamic Host Configuration Protocol) memberikan konfigurasi IP secara otomatis ke klien. Pastikan server DHCP aktif, pool IP cukup, dan klien berada pada VLAN/jaringan yang sama dengan server DHCP atau DHCP Relay sudah dikonfigurasi.';
        }

        return 'Pertanyaan yang menarik! Saat ini aku masih dalam mode pengembangan dan dapat menjawab seputar subnetting VLSM, CIDR, ping/RTO, dan DHCP. Silakan tanyakan lebih spesifik ya, atau cek modul pertemuan terkait.';
    }
}

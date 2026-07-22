<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SoalKuis;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function store(Request $request, $pertemuan_id)
    {
        $data = $request->validate([
            'pertanyaan' => 'required|string',
            'pilihan_a' => 'required|string',
            'pilihan_b' => 'required|string',
            'pilihan_c' => 'required|string',
            'pilihan_d' => 'required|string',
            'kunci_jawaban' => 'required|in:A,B,C,D',
            'penjelasan' => 'nullable|string',
        ]);

        $data['pertemuan_id'] = $pertemuan_id;

        SoalKuis::create($data);

        return redirect()->route('admin.materi.show', $pertemuan_id)->with('success', 'Soal kuis berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $soal = SoalKuis::findOrFail($id);
        return view('admin.quiz.edit', compact('soal'));
    }

    public function update(Request $request, $id)
    {
        $soal = SoalKuis::findOrFail($id);

        $data = $request->validate([
            'pertanyaan' => 'required|string',
            'pilihan_a' => 'required|string',
            'pilihan_b' => 'required|string',
            'pilihan_c' => 'required|string',
            'pilihan_d' => 'required|string',
            'kunci_jawaban' => 'required|in:A,B,C,D',
            'penjelasan' => 'nullable|string',
        ]);

        $soal->update($data);

        return redirect()->route('admin.materi.show', $soal->pertemuan_id)->with('success', 'Soal kuis berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $soal = SoalKuis::findOrFail($id);
        $pertemuan_id = $soal->pertemuan_id;
        $soal->delete();

        return redirect()->route('admin.materi.show', $pertemuan_id)->with('success', 'Soal kuis berhasil dihapus.');
    }

    public function generateByAI(Request $request, $pertemuan_id)
    {
        $jumlahSoal = $request->json('jumlah_soal', $request->input('jumlah_soal', 5));

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(180)->post('http://127.0.0.1:5050/generate-quiz', [
                'pertemuan_id' => (int) $pertemuan_id,
                'jumlah_soal' => (int) $jumlahSoal,
            ]);

            if ($response->successful() && $response->json('success')) {
                $soalList = $response->json('data.soal');
                $insertedCount = 0;

                foreach ($soalList as $soal) {
                    SoalKuis::create([
                        'pertemuan_id' => $pertemuan_id,
                        'pertanyaan' => $soal['pertanyaan'],
                        'pilihan_a' => $soal['pilihan_a'],
                        'pilihan_b' => $soal['pilihan_b'],
                        'pilihan_c' => $soal['pilihan_c'],
                        'pilihan_d' => $soal['pilihan_d'],
                        'kunci_jawaban' => $soal['kunci_jawaban'],
                        'penjelasan' => $soal['pembahasan'] ?? $soal['penjelasan'] ?? null,
                    ]);
                    $insertedCount++;
                }

                return response()->json([
                    'success' => true,
                    'message' => "Berhasil generate $insertedCount soal kuis menggunakan AI.",
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal generate kuis dari AI: ' . ($response->json('message') ?? 'Error tidak diketahui.'),
            ], 422);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('AI Quiz Generation Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal terhubung ke AI Engine: ' . $e->getMessage(),
            ], 500);
        }
    }
}
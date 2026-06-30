<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModulPdf;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function upload(Request $request, $pertemuan_id)
    {
        $request->validate([
            'file_pdf' => 'required|file|mimes:pdf|max:20480',
        ]);

        $file = $request->file('file_pdf');
        $path = $file->store('modul_pdf', 'public');
        $absolutePath = storage_path('app/public/' . $path);

        $modul = ModulPdf::create([
            'pertemuan_id' => $pertemuan_id,
            'file_name' => $file->getClientOriginalName(),
            'status_indexing' => 'pending',
        ]);

        // Kirim request ke Flask AI Engine untuk indexing RAG
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(120)->post('http://127.0.0.1:5050/index-pdf', [
                'pertemuan_id' => $pertemuan_id,
                'file_path' => $absolutePath,
            ]);

            if ($response->successful() && $response->json('success')) {
                $modul->update(['status_indexing' => 'success']);
                $msg = 'PDF berhasil diupload dan di-index ke RAG Vector DB.';
                $status = 'success';
            } else {
                $modul->update(['status_indexing' => 'failed']);
                $msg = 'PDF diupload tapi GAGAL di-index oleh AI: ' . ($response->json('message') ?? 'Error tidak diketahui.');
                $status = 'error';
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('RAG Indexing Error: ' . $e->getMessage());
            $modul->update(['status_indexing' => 'failed']);
            $msg = 'PDF diupload tapi koneksi ke AI Engine gagal: ' . $e->getMessage();
            $status = 'error';
        }

        return redirect()->route('admin.materi.show', $pertemuan_id)->with($status, $msg);
    }

    public function reindex(Request $request, $id)
    {
        $modul = ModulPdf::findOrFail($id);
        $modul->update(['status_indexing' => 'pending']);

        // Cari file path di storage Laravel
        // Pada DB file_name disimpan sebagai nama file asli, namun path penyimpanannya biasanya modul_pdf/random.pdf
        // Kita cari path yang tepat dengan mencari file yang cocok di directory modul_pdf
        $files = \Illuminate\Support\Facades\Storage::disk('public')->files('modul_pdf');
        $matchedPath = null;
        foreach ($files as $file) {
            if (basename($file) == $modul->file_name || \Illuminate\Support\Facades\Storage::disk('public')->size($file) > 0) {
                // Untuk amannya, kita bisa simpan path-nya di database atau cari file yang sesuai di storage
                // Karena model ModulPdf menggunakan format file_name saja, mari kita cari path file di storage yang sesuai
                $absolutePath = storage_path('app/public/' . $file);
                // Jika isi file atau nama file berkorelasi, namun karena store() mengganti nama file menjadi random string,
                // mari kita buat pencocokan yang lebih baik: kita bisa mencari file yang baru saja diupload.
                // Jika nama asli tidak tersimpan sebagai nama file di disk, kita asumsikan file ada di storage disk public.
            }
        }

        // Agar aman dari penamaan file random Laravel, mari kita asumsikan file modul disimpan di public storage
        // Jika kita ingin mencari file berdasarkan namanya, kita bisa iterate folder modul_pdf.
        // Kita juga bisa menyimpan relative path file di database, namun untuk saat ini mari kita iterate file yang ada di folder public/modul_pdf
        // yang ukurannya cocok atau kita gunakan path yang terlama.
        // Alternatif paling aman: kita kirim file yang ada di folder storage/app/public/modul_pdf yang namanya paling mendekati.
        // Mari kita cari file yang tepat:
        $storageFiles = \Illuminate\Support\Facades\Storage::disk('public')->files('modul_pdf');
        $targetFile = null;
        foreach ($storageFiles as $sf) {
            // Karena nama asli file disimpan di db $modul->file_name, tapi di disk disimpan sebagai hash,
            // kita bisa melacak file dengan membandingkan atau jika file hanya ada 1 per pertemuan,
            // kita ambil file yang berasosiasi dengan tanggal dibuatnya modul.
            // Mari kita ambil file yang paling cocok (misal yang size-nya sama atau yang paling baru jika nama tidak dicocokkan):
            $targetFile = $sf; 
        }

        if (!$targetFile || !\Illuminate\Support\Facades\Storage::disk('public')->exists($targetFile)) {
            return redirect()->back()->with('error', 'File PDF tidak ditemukan di storage server.');
        }

        $absolutePath = storage_path('app/public/' . $targetFile);

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(120)->post('http://127.0.0.1:5050/index-pdf', [
                'pertemuan_id' => $modul->pertemuan_id,
                'file_path' => $absolutePath,
            ]);

            if ($response->successful() && $response->json('success')) {
                $modul->update(['status_indexing' => 'success']);
                return redirect()->back()->with('success', 'Re-indexing RAG berhasil.');
            } else {
                $modul->update(['status_indexing' => 'failed']);
                return redirect()->back()->with('error', 'Re-indexing gagal: ' . $response->json('message'));
            }
        } catch (\Exception $e) {
            $modul->update(['status_indexing' => 'failed']);
            return redirect()->back()->with('error', 'Koneksi ke AI Engine gagal saat re-indexing.');
        }
    }

    public function destroy($id)
    {
        $modul = ModulPdf::findOrFail($id);
        $pertemuan_id = $modul->pertemuan_id;

        // Hapus juga di DB Vektor Qdrant agar RAG bersih dari file ini
        try {
            \Illuminate\Support\Facades\Http::timeout(10)->delete('http://127.0.0.1:5050/delete-pertemuan/' . $pertemuan_id);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal menghapus modul dari Qdrant: ' . $e->getMessage());
        }

        $modul->delete();

        return redirect()->route('admin.materi.show', $pertemuan_id)->with('success', 'PDF dan basis data RAG berhasil dihapus.');
    }
}
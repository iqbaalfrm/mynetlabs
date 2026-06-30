<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pertemuan;
use App\Models\TopikMateri;
use App\Models\SoalKuis;
use Illuminate\Http\Request;

class MateriController extends Controller
{
    // Index: tab semester 1 & 2
    public function index()
    {
        $pertemuanSemester1 = Pertemuan::where('semester', '1')->orderBy('nomor_urut')->get();
        $pertemuanSemester2 = Pertemuan::where('semester', '2')->orderBy('nomor_urut')->get();
        return view('admin.materi.index', compact('pertemuanSemester1', 'pertemuanSemester2'));
    }

    // Detail pertemuan (tabs: topik, PDF, quiz)
    public function show($id)
    {
        $pertemuan = Pertemuan::with(['topikMateris' => fn($q) => $q->orderBy('urutan'), 'soalKuis', 'modulPdfs'])->findOrFail($id);
        return view('admin.materi.show', compact('pertemuan'));
    }

    public function create()
    {
        return view('admin.materi.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nomor_urut' => 'required|integer|min:1',
            'judul' => 'required|string|max:150',
            'deskripsi' => 'nullable|string',
            'semester' => 'required|in:1,2',
            'warna_tema' => 'nullable|string|max:7',
        ]);

        $data['warna_tema'] = $data['warna_tema'] ?? '#3B82F6';

        Pertemuan::create($data);

        return redirect()->route('admin.materi.index')->with('success', 'Pertemuan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $pertemuan = Pertemuan::findOrFail($id);
        return view('admin.materi.edit', compact('pertemuan'));
    }

    public function update(Request $request, $id)
    {
        $pertemuan = Pertemuan::findOrFail($id);

        $data = $request->validate([
            'nomor_urut' => 'required|integer|min:1',
            'judul' => 'required|string|max:150',
            'deskripsi' => 'nullable|string',
            'semester' => 'required|in:1,2',
            'warna_tema' => 'nullable|string|max:7',
        ]);

        $pertemuan->update($data);

        return redirect()->route('admin.materi.index')->with('success', 'Pertemuan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $pertemuan = Pertemuan::findOrFail($id);
        $pertemuan->delete();

        return redirect()->route('admin.materi.index')->with('success', 'Pertemuan berhasil dihapus.');
    }
}
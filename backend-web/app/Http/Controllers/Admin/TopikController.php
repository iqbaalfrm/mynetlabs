<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TopikMateri;
use Illuminate\Http\Request;

class TopikController extends Controller
{
    public function store(Request $request, $pertemuan_id)
    {
        $data = $request->validate([
            'judul' => 'required|string|max:150',
            'urutan' => 'nullable|integer|min:1',
            'deskripsi' => 'nullable|string',
            'isi_materi' => 'required|string',
            'file_materi' => 'nullable|file|mimes:pdf,ppt,pptx,doc,docx|max:10240',
        ]);

        $data['pertemuan_id'] = $pertemuan_id;

        if ($request->hasFile('file_materi')) {
            $data['file_materi'] = $request->file('file_materi')->store('materi/files', 'public');
        }

        TopikMateri::create($data);

        return redirect()->route('admin.materi.show', $pertemuan_id)->with('success', 'Topik materi berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $topik = TopikMateri::findOrFail($id);
        return view('admin.topik.edit', compact('topik'));
    }

    public function update(Request $request, $id)
    {
        $topik = TopikMateri::findOrFail($id);

        $data = $request->validate([
            'judul' => 'required|string|max:150',
            'urutan' => 'nullable|integer|min:1',
            'deskripsi' => 'nullable|string',
            'isi_materi' => 'required|string',
            'file_materi' => 'nullable|file|mimes:pdf,ppt,pptx,doc,docx|max:10240',
        ]);

        if ($request->hasFile('file_materi')) {
            $data['file_materi'] = $request->file('file_materi')->store('materi/files', 'public');
        }

        $topik->update($data);

        return redirect()->route('admin.materi.show', $topik->pertemuan_id)->with('success', 'Topik materi berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $topik = TopikMateri::findOrFail($id);
        $pertemuan_id = $topik->pertemuan_id;
        $topik->delete();

        return redirect()->route('admin.materi.show', $pertemuan_id)->with('success', 'Topik materi berhasil dihapus.');
    }
}
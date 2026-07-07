<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index()
    {
        $kelasList = Kelas::withCount('siswa')->with('waliKelas')->get();
        $guruList = User::where('role', 'guru')->get();
        return view('admin.kelas.index', compact('kelasList', 'guruList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kelas' => 'required|string|max:50',
            'wali_kelas_id' => 'nullable|exists:users,id',
        ]);

        Kelas::create($request->only('nama_kelas', 'wali_kelas_id'));

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_kelas' => 'required|string|max:50',
            'wali_kelas_id' => 'nullable|exists:users,id',
        ]);

        $kelas = Kelas::findOrFail($id);
        $kelas->update($request->only('nama_kelas', 'wali_kelas_id'));

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $kelas = Kelas::findOrFail($id);
        
        if ($kelas->siswa()->count() > 0) {
            return redirect()->back()->with('error', 'Kelas tidak bisa dihapus karena masih ada siswa terdaftar.');
        }

        $kelas->delete();

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil dihapus.');
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Kelas;
use App\Http\Requests\StoreSiswaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SiswaController extends Controller
{
    /**
     * Menampilkan daftar data siswa.
     */
    public function index(Request $request)
    {
        $query = User::query()->where('role', 'siswa');

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $siswa = $query->with('kelasRelation')->orderBy('nama', 'asc')->paginate(15);
        $kelasList = Kelas::orderBy('nama_kelas')->get();

        return view('admin.siswa.index', compact('siswa', 'kelasList'));
    }

    /**
     * Menampilkan formulir untuk membuat siswa baru.
     */
    public function create()
    {
        $kelasList = Kelas::orderBy('nama_kelas')->get();
        return view('admin.siswa.create', compact('kelasList'));
    }

    /**
     * Menyimpan data siswa baru ke database.
     */
    public function store(StoreSiswaRequest $request)
    {
        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = 'siswa';

        User::create($validated);

        return redirect()->route('admin.siswa.index')->with('success', 'Akun siswa berhasil ditambahkan.');
    }

    /**
     * Menampilkan formulir edit data siswa tertentu.
     */
    public function edit($id)
    {
        $user = User::where('role', 'siswa')->findOrFail($id);
        $kelasList = Kelas::orderBy('nama_kelas')->get();
        return view('admin.siswa.edit', compact('user', 'kelasList'));
    }

    /**
     * Memperbarui data siswa tertentu di database.
     */
    public function update(StoreSiswaRequest $request, $id)
    {
        $user = User::where('role', 'siswa')->findOrFail($id);
        $validated = $request->validated();

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.siswa.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    /**
     * Menghapus data siswa tertentu dari database.
     */
    public function destroy($id)
    {
        $user = User::where('role', 'siswa')->findOrFail($id);
        $user->delete();

        return redirect()->route('admin.siswa.index')->with('success', 'Akun siswa berhasil dihapus.');
    }

    /**
     * Mengubah status keaktifan akun siswa (aktif/nonaktif).
     */
    public function toggleStatus($id)
    {
        $user = User::where('role', 'siswa')->findOrFail($id);
        $user->status = $user->status === 'aktif' ? 'nonaktif' : 'aktif';
        $user->save();

        return redirect()->route('admin.siswa.index')->with('success', 'Status akun siswa berhasil diubah.');
    }
}

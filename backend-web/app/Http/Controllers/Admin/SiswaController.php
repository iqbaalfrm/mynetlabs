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
     * Display a listing of the resource.
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kelasList = Kelas::orderBy('nama_kelas')->get();
        return view('admin.siswa.create', compact('kelasList'));
    }

    /**
     * Store a newly created resource in storage.
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
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user = User::where('role', 'siswa')->findOrFail($id);
        $kelasList = Kelas::orderBy('nama_kelas')->get();
        return view('admin.siswa.edit', compact('user', 'kelasList'));
    }

    /**
     * Update the specified resource in storage.
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
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = User::where('role', 'siswa')->findOrFail($id);
        $user->delete();

        return redirect()->route('admin.siswa.index')->with('success', 'Akun siswa berhasil dihapus.');
    }

    /**
     * Toggle the active status of the specified student.
     */
    public function toggleStatus($id)
    {
        $user = User::where('role', 'siswa')->findOrFail($id);
        $user->status = $user->status === 'aktif' ? 'nonaktif' : 'aktif';
        $user->save();

        return redirect()->route('admin.siswa.index')->with('success', 'Status akun siswa berhasil diubah.');
    }
}

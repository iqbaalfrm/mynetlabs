<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
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

        $users = $query->with('kelasRelation')->orderBy('created_at', 'desc')->paginate(15);
        $kelasList = Kelas::orderBy('nama_kelas')->get();

        return view('admin.users.index', compact('users', 'kelasList'));
    }

    public function create()
    {
        $kelasList = Kelas::orderBy('nama_kelas')->get();
        return view('admin.users.create', compact('kelasList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'nama' => 'required|string|max:100',
            'password' => 'required|string|min:6',
            'role' => 'required|in:guru,siswa',
            'kelas_id' => 'nullable|exists:kelas,id',
            'foto_profil' => 'nullable|image|max:2048',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        if ($request->hasFile('foto_profil')) {
            $validated['foto_profil'] = $request->file('foto_profil')->store('profiles', 'public');
        }

        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $kelasList = Kelas::orderBy('nama_kelas')->get();
        return view('admin.users.edit', compact('user', 'kelasList'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'nama' => 'required|string|max:100',
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:guru,siswa',
            'kelas_id' => 'nullable|exists:kelas,id',
            'foto_profil' => 'nullable|image|max:2048',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        if ($request->hasFile('foto_profil')) {
            $validated['foto_profil'] = $request->file('foto_profil')->store('profiles', 'public');
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['success' => true, 'message' => 'User berhasil dihapus.']);
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatHistory;
use App\Models\User;
use App\Models\Pertemuan;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $query = ChatHistory::with(['siswa', 'pertemuan']);

        if ($request->filled('siswa_id')) {
            $query->where('siswa_id', $request->siswa_id);
        }

        if ($request->filled('pertemuan_id')) {
            $query->where('pertemuan_id', $request->pertemuan_id);
        }

        if ($request->filled('sender')) {
            $query->where('sender', $request->sender);
        }

        $chatList = $query->latest()->paginate(15)->appends($request->query());
        $siswaList = User::where('role', 'siswa')->get();
        $pertemuanList = Pertemuan::orderBy('nomor_urut')->get();

        return view('admin.chat.index', compact('chatList', 'siswaList', 'pertemuanList'));
    }

    public function destroy($id)
    {
        $chat = ChatHistory::findOrFail($id);
        $chat->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Pesan chat berhasil dihapus.']);
        }

        return redirect()->route('admin.chat.index')->with('success', 'Pesan chat berhasil dihapus.');
    }

    public function destroyBySiswa($siswaId)
    {
        ChatHistory::where('siswa_id', $siswaId)->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Semua riwayat chat siswa berhasil dihapus.']);
        }

        return redirect()->route('admin.chat.index')->with('success', 'Semua riwayat chat siswa berhasil dihapus.');
    }
}
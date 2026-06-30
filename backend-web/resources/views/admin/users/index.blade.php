@extends('layouts.master')

@section('title', 'Kelola Users - NetLabs Admin')

@section('content')
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="card-title mb-0">Kelola Users</h4>
          <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
            <i class="ti-plus"></i> Tambah User
          </a>
        </div>

        @if(session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Filter -->
        <form method="GET" class="row mb-3">
          <div class="col-md-3">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama/username..." value="{{ request('search') }}">
          </div>
          <div class="col-md-2">
            <select name="role" class="form-select form-select-sm">
              <option value="">Semua Role</option>
              <option value="guru" {{ request('role') == 'guru' ? 'selected' : '' }}>Guru</option>
              <option value="siswa" {{ request('role') == 'siswa' ? 'selected' : '' }}>Siswa</option>
            </select>
          </div>
          <div class="col-md-2">
            <select name="kelas_id" class="form-select form-select-sm">
              <option value="">Semua Kelas</option>
              @foreach($kelasList as $kelas)
                <option value="{{ $kelas->id }}" {{ request('kelas_id') == $kelas->id ? 'selected' : '' }}>{{ $kelas->nama_kelas }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-sm btn-outline-secondary">Filter</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-danger">Reset</a>
          </div>
        </form>

        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>#</th>
                <th>Username</th>
                <th>Nama</th>
                <th>Role</th>
                <th>Kelas</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($users as $user)
                <tr>
                  <td>{{ $loop->iteration + $users->firstItem() - 1 }}</td>
                  <td>
                    @if($user->foto_profil)
                      <img src="{{ $user->foto_profil_url }}" class="rounded-circle me-1" width="28" height="28" style="object-fit:cover;">
                    @endif
                    {{ $user->username }}
                  </td>
                  <td>{{ $user->nama }}</td>
                  <td>
                    <span class="badge {{ $user->role == 'guru' ? 'bg-primary' : 'bg-info' }}">
                      {{ ucfirst($user->role) }}
                    </span>
                  </td>
                  <td>{{ $user->kelasRelation->nama_kelas ?? '-' }}</td>
                  <td>
                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-outline-secondary btn-sm">
                      <i class="ti-pencil"></i>
                    </a>
                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus user ini?')">
                      @csrf @method('DELETE')
                      <button class="btn btn-outline-danger btn-sm"><i class="ti-trash"></i></button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center text-muted py-3">Belum ada user.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="mt-3">
          {{ $users->appends(request()->query())->links() }}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
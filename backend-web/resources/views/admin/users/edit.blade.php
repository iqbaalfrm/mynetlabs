@extends('layouts.master')

@section('title', 'Edit Pengguna - NetLabs Admin')

@section('content')
<div class="row">
  <div class="col-md-8 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Edit Pengguna: {{ $user->nama }}</h4>

        @if($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0">
              @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form method="POST" action="{{ route('admin.users.update', $user->id) }}" enctype="multipart/form-data">
          @csrf
          @method('PUT')

          <div class="mb-3">
            <label class="form-label">Username <span class="text-danger">*</span></label>
            <input type="text" name="username" class="form-control" value="{{ old('username', $user->username) }}" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Nama <span class="text-danger">*</span></label>
            <input type="text" name="nama" class="form-control" value="{{ old('nama', $user->nama) }}" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Password <small class="text-muted">(Kosongkan jika tidak ingin diubah)</small></label>
            <input type="password" name="password" class="form-control" minlength="6">
          </div>

          <div class="mb-3">
            <label class="form-label">Role <span class="text-danger">*</span></label>
            <select name="role" class="form-select" required>
              <option value="guru" {{ old('role', $user->role) == 'guru' ? 'selected' : '' }}>Guru</option>
              <option value="siswa" {{ old('role', $user->role) == 'siswa' ? 'selected' : '' }}>Siswa</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Kelas</label>
            <select name="kelas_id" class="form-select">
              <option value="">Tanpa Kelas</option>
              @foreach($kelasList as $kelas)
                <option value="{{ $kelas->id }}" {{ old('kelas_id', $user->kelas_id) == $kelas->id ? 'selected' : '' }}>{{ $kelas->nama_kelas }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Foto Profil</label>
            @if($user->foto_profil_url)
              <div class="mb-2">
                <img src="{{ $user->foto_profil_url }}" class="rounded-circle" width="64" height="64" style="object-fit:cover;">
              </div>
            @endif
            <input type="file" name="foto_profil" class="form-control" accept="image/*">
          </div>

          <button type="submit" class="btn btn-primary me-2">Update</button>
          <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Batal</a>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
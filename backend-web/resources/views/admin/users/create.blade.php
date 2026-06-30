@extends('layouts.master')

@section('title', 'Tambah User - NetLabs Admin')

@section('content')
<div class="row">
  <div class="col-md-8 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Tambah User</h4>

        @if($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0">
              @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data">
          @csrf

          <div class="mb-3">
            <label class="form-label">Username <span class="text-danger">*</span></label>
            <input type="text" name="username" class="form-control" value="{{ old('username') }}" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Nama <span class="text-danger">*</span></label>
            <input type="text" name="nama" class="form-control" value="{{ old('nama') }}" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Password <span class="text-danger">*</span></label>
            <input type="password" name="password" class="form-control" required minlength="6">
          </div>

          <div class="mb-3">
            <label class="form-label">Role <span class="text-danger">*</span></label>
            <select name="role" class="form-select" required>
              <option value="">Pilih Role</option>
              <option value="guru" {{ old('role') == 'guru' ? 'selected' : '' }}>Guru</option>
              <option value="siswa" {{ old('role') == 'siswa' ? 'selected' : '' }}>Siswa</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Kelas</label>
            <select name="kelas_id" class="form-select">
              <option value="">Tanpa Kelas</option>
              @foreach($kelasList as $kelas)
                <option value="{{ $kelas->id }}" {{ old('kelas_id') == $kelas->id ? 'selected' : '' }}>{{ $kelas->nama_kelas }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Foto Profil</label>
            <input type="file" name="foto_profil" class="form-control" accept="image/*">
          </div>

          <button type="submit" class="btn btn-primary me-2">Simpan</button>
          <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Batal</a>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
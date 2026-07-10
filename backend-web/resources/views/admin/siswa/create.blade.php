@extends('layouts.master')

@section('title', 'Tambah Siswa - NetLabs Admin')

@section('content')
<div class="row">
  <div class="col-md-8 grid-margin stretch-card mx-auto">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h4 class="card-title mb-1" style="font-weight: 700;">Tambah Akun Siswa Baru</h4>
            <p class="text-muted mb-0" style="font-size: 13px;">Silakan isi formulir di bawah ini untuk menambahkan siswa.</p>
          </div>
          <a href="{{ route('admin.siswa.index') }}" class="btn btn-outline-secondary btn-sm">
            Kembali
          </a>
        </div>

        <form action="{{ route('admin.siswa.store') }}" method="POST" class="forms-sample">
          @csrf

          <!-- Nama Lengkap -->
          <div class="form-group mb-3">
            <label for="nama" class="form-label" style="font-weight: 600;">Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" placeholder="Masukkan nama lengkap siswa..." value="{{ old('nama') }}" required>
            @error('nama')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- NIS / Username -->
          <div class="form-group mb-3">
            <label for="username" class="form-label" style="font-weight: 600;">Nomor Induk Siswa (NIS) <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" placeholder="Masukkan NIS sebagai username login..." value="{{ old('username') }}" required>
            <small class="text-muted mt-1 d-block">NIS ini akan digunakan siswa untuk login ke aplikasi mobile.</small>
            @error('username')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Kelas Dropdown -->
          <div class="form-group mb-3">
            <label for="kelas_id" class="form-label" style="font-weight: 600;">Kelas TKJ <span class="text-danger">*</span></label>
            <select class="form-select @error('kelas_id') is-invalid @enderror" id="kelas_id" name="kelas_id" required>
              <option value="">-- Pilih Kelas --</option>
              @foreach($kelasList as $kelas)
                <option value="{{ $kelas->id }}" {{ old('kelas_id') == $kelas->id ? 'selected' : '' }}>{{ $kelas->nama_kelas }}</option>
              @endforeach
            </select>
            @error('kelas_id')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Status Akun -->
          <div class="form-group mb-3">
            <label for="status" class="form-label" style="font-weight: 600;">Status Keaktifan Akun <span class="text-danger">*</span></label>
            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
              <option value="aktif" {{ old('status', 'aktif') == 'aktif' ? 'selected' : '' }}>Aktif (Dapat Login)</option>
              <option value="nonaktif" {{ old('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif (Ditangguhkan)</option>
            </select>
            @error('status')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <hr class="my-4" style="border-color: #E5E7EB;">

          <!-- Password -->
          <div class="form-group mb-3">
            <label for="password" class="form-label" style="font-weight: 600;">Kata Sandi Login <span class="text-danger">*</span></label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Minimal 8 karakter..." required>
            @error('password')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Konfirmasi Password -->
          <div class="form-group mb-4">
            <label for="password_confirmation" class="form-label" style="font-weight: 600;">Konfirmasi Kata Sandi <span class="text-danger">*</span></label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Ulangi kata sandi..." required>
          </div>

          <button type="submit" class="btn btn-primary me-2">Simpan Akun</button>
          <a href="{{ route('admin.siswa.index') }}" class="btn btn-light">Batal</a>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

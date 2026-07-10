@extends('layouts.master')

@section('title', 'Edit Siswa - NetLabs Admin')

@section('content')
<div class="row">
  <div class="col-md-8 grid-margin stretch-card mx-auto">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h4 class="card-title mb-1" style="font-weight: 700;">Edit Akun Siswa</h4>
            <p class="text-muted mb-0" style="font-size: 13px;">Silakan perbarui detail data siswa di bawah ini.</p>
          </div>
          <a href="{{ route('admin.siswa.index') }}" class="btn btn-outline-secondary btn-sm">
            Kembali
          </a>
        </div>

        <form action="{{ route('admin.siswa.update', $user->id) }}" method="POST" class="forms-sample">
          @csrf
          @method('PUT')

          <!-- Nama Lengkap -->
          <div class="form-group mb-3">
            <label for="nama" class="form-label" style="font-weight: 600;">Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" placeholder="Masukkan nama lengkap siswa..." value="{{ old('nama', $user->nama) }}" required>
            @error('nama')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- NIS / Username -->
          <div class="form-group mb-3">
            <label for="username" class="form-label" style="font-weight: 600;">Nomor Induk Siswa (NIS) <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" placeholder="Masukkan NIS sebagai username login..." value="{{ old('username', $user->username) }}" required>
            <small class="text-muted mt-1 d-block">NIS ini digunakan siswa untuk login ke aplikasi mobile.</small>
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
                <option value="{{ $kelas->id }}" {{ old('kelas_id', $user->kelas_id) == $kelas->id ? 'selected' : '' }}>{{ $kelas->nama_kelas }}</option>
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
              <option value="aktif" {{ old('status', $user->status) == 'aktif' ? 'selected' : '' }}>Aktif (Dapat Login)</option>
              <option value="nonaktif" {{ old('status', $user->status) == 'nonaktif' ? 'selected' : '' }}>Nonaktif (Ditangguhkan)</option>
            </select>
            @error('status')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <hr class="my-4" style="border-color: #E5E7EB;">
          
          <div class="bg-light p-3 rounded mb-3">
            <h6 style="font-weight: 700;" class="mb-1 text-dark"><i class="ti-lock me-1 text-primary"></i> Perbarui Kata Sandi (Opsional)</h6>
            <p class="text-muted mb-0" style="font-size: 12px;">Kosongkan jika kata sandi siswa tidak ingin diubah.</p>
          </div>

          <!-- Password -->
          <div class="form-group mb-3">
            <label for="password" class="form-label" style="font-weight: 600;">Kata Sandi Baru</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Kosongkan jika tidak diubah (min 8 karakter)...">
            @error('password')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Konfirmasi Password -->
          <div class="form-group mb-4">
            <label for="password_confirmation" class="form-label" style="font-weight: 600;">Konfirmasi Kata Sandi Baru</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Ulangi kata sandi baru...">
          </div>

          <button type="submit" class="btn btn-primary me-2">Perbarui Akun</button>
          <a href="{{ route('admin.siswa.index') }}" class="btn btn-light">Batal</a>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

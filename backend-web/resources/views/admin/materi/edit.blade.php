@extends('layouts.master')

@section('title', 'Edit Pertemuan - NetLabs Admin')

@section('content')
<div class="row">
  <div class="col-md-8 grid-margin stretch-card mx-auto">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Edit Pertemuan</h4>
        <form action="{{ route('admin.materi.update', $pertemuan->id) }}" method="POST">
          @csrf
          @method('PUT')
          <div class="mb-3">
            <label class="form-label">Nomor Urut</label>
            <input type="number" name="nomor_urut" class="form-control @error('nomor_urut') is-invalid @enderror" value="{{ old('nomor_urut', $pertemuan->nomor_urut) }}" required>
            @error('nomor_urut')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="mb-3">
            <label class="form-label">Judul</label>
            <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror" value="{{ old('judul', $pertemuan->judul) }}" required>
            @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="mb-3">
            <label class="form-label">Deskripsi Singkat (Poin Utama)</label>
            <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="2">{{ old('deskripsi', $pertemuan->deskripsi) }}</textarea>
            @error('deskripsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="mb-3">
            <label class="form-label">Isi Materi Pembelajaran</label>
            <textarea name="isi_materi" class="form-control @error('isi_materi') is-invalid @enderror" rows="10" placeholder="Tuliskan isi materi lengkap di sini..." required>{{ old('isi_materi', $pertemuan->isi_materi) }}</textarea>
            @error('isi_materi')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="mb-3">
            <label class="form-label">Semester</label>
            <select name="semester" class="form-select @error('semester') is-invalid @enderror" required>
              <option value="">-- Pilih Semester --</option>
              <option value="1" {{ old('semester', $pertemuan->semester) == '1' ? 'selected' : '' }}>Semester 1</option>
              <option value="2" {{ old('semester', $pertemuan->semester) == '2' ? 'selected' : '' }}>Semester 2</option>
            </select>
            @error('semester')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="mb-3">
            <label class="form-label">Warna Tema</label>
            <input type="color" name="warna_tema" class="form-control form-control-color @error('warna_tema') is-invalid @enderror" value="{{ old('warna_tema', $pertemuan->warna_tema ?? '#3B82F6') }}">
            @error('warna_tema')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('admin.materi.index') }}" class="btn btn-light">Batal</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
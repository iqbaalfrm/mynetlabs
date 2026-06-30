@extends('layouts.master')

@section('title', 'Edit Topik Materi - NetLabs Admin')

@section('content')
<div class="row mb-3">
  <div class="col-12">
    <a href="{{ route('admin.materi.show', $topik->pertemuan_id) }}" class="btn btn-light btn-sm">
      <i class="ti-arrow-left"></i> Kembali
    </a>
  </div>
</div>

<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Edit Topik Materi</h4>

        <form action="{{ route('admin.topik.update', $topik->id) }}" method="POST" enctype="multipart/form-data">
          @csrf @method('PUT')

          <div class="mb-3">
            <label class="form-label">Judul</label>
            <input type="text" name="judul" class="form-control" value="{{ old('judul', $topik->judul) }}" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Urutan</label>
            <input type="number" name="urutan" class="form-control" value="{{ old('urutan', $topik->urutan) }}">
          </div>

          <div class="mb-3">
            <label class="form-label">Deskripsi</label>
            <textarea name="deskripsi" class="form-control" rows="2">{{ old('deskripsi', $topik->deskripsi) }}</textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Isi Materi</label>
            <textarea name="isi_materi" class="form-control" rows="8" required>{{ old('isi_materi', $topik->isi_materi) }}</textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">File Materi (PDF/PPT/DOC) — Biarkan kosong jika tidak ingin mengganti</label>
            <input type="file" name="file_materi" class="form-control" accept=".pdf,.ppt,.pptx,.doc,.docx">
            @if($topik->file_materi)
              <small class="text-muted">File saat ini: <a href="{{ asset('storage/'.$topik->file_materi) }}" target="_blank">{{ basename($topik->file_materi) }}</a></small>
            @endif
          </div>

          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
          <a href="{{ route('admin.materi.show', $topik->pertemuan_id) }}" class="btn btn-light">Batal</a>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
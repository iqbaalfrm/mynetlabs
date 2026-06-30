@extends('layouts.master')

@section('title', 'Edit Soal Kuis - NetLabs Admin')

@section('content')
<div class="row mb-3">
  <div class="col-12">
    <a href="{{ route('admin.materi.show', $soal->pertemuan_id) }}" class="btn btn-light btn-sm">
      <i class="ti-arrow-left"></i> Kembali
    </a>
  </div>
</div>

<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Edit Soal Kuis</h4>

        <form action="{{ route('admin.quiz.update', $soal->id) }}" method="POST">
          @csrf @method('PUT')

          <div class="mb-3">
            <label class="form-label">Pertanyaan</label>
            <textarea name="pertanyaan" class="form-control" rows="3" required>{{ old('pertanyaan', $soal->pertanyaan) }}</textarea>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Pilihan A</label>
              <input type="text" name="pilihan_a" class="form-control" value="{{ old('pilihan_a', $soal->pilihan_a) }}" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Pilihan B</label>
              <input type="text" name="pilihan_b" class="form-control" value="{{ old('pilihan_b', $soal->pilihan_b) }}" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Pilihan C</label>
              <input type="text" name="pilihan_c" class="form-control" value="{{ old('pilihan_c', $soal->pilihan_c) }}" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Pilihan D</label>
              <input type="text" name="pilihan_d" class="form-control" value="{{ old('pilihan_d', $soal->pilihan_d) }}" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Kunci Jawaban</label>
              <select name="kunci_jawaban" class="form-select" required>
                <option value="A" {{ old('kunci_jawaban', $soal->kunci_jawaban) === 'A' ? 'selected' : '' }}>A</option>
                <option value="B" {{ old('kunci_jawaban', $soal->kunci_jawaban) === 'B' ? 'selected' : '' }}>B</option>
                <option value="C" {{ old('kunci_jawaban', $soal->kunci_jawaban) === 'C' ? 'selected' : '' }}>C</option>
                <option value="D" {{ old('kunci_jawaban', $soal->kunci_jawaban) === 'D' ? 'selected' : '' }}>D</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Penjelasan</label>
              <textarea name="penjelasan" class="form-control" rows="2">{{ old('penjelasan', $soal->penjelasan) }}</textarea>
            </div>
          </div>

          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
          <a href="{{ route('admin.materi.show', $soal->pertemuan_id) }}" class="btn btn-light">Batal</a>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
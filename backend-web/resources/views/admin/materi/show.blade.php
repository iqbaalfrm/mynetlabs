@extends('layouts.master')

@section('title', $pertemuan->judul . ' - NetLabs Admin')

@push('styles')
<style>
  .tab-icon { margin-right: 6px; }
  .topik-item { border-left: 3px solid {{ $pertemuan->warna_tema ?? '#3B82F6' }}; padding-left: 12px; margin-bottom: 12px; }
  .status-pending { color: #f59e0b; }
  .status-processing { color: #3B82F6; }
  .status-success { color: #10B981; }
  .status-failed { color: #EF4444; }
</style>
@endpush

@section('content')
<div class="row mb-3">
  <div class="col-12">
    <a href="{{ route('admin.materi.index') }}" class="btn btn-light btn-sm">
      <i class="ti-arrow-left"></i> Kembali
    </a>
  </div>
</div>

<div class="row mb-3">
  <div class="col-12">
    <div class="card" style="border-left: 5px solid {{ $pertemuan->warna_tema ?? '#3B82F6' }};">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <span class="badge bg-primary mb-2">Pertemuan {{ $pertemuan->nomor_urut }} — Semester {{ $pertemuan->semester }}</span>
            <h3 class="card-title mb-1">{{ $pertemuan->judul }}</h3>
            @if($pertemuan->deskripsi)
              <p class="text-muted mb-0">{{ $pertemuan->deskripsi }}</p>
            @endif
          </div>
          <a href="{{ route('admin.materi.edit', $pertemuan->id) }}" class="btn btn-outline-secondary btn-sm">
            <i class="ti-pencil"></i> Edit
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        @if(session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <ul class="nav nav-tabs" id="detailTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="topik-tab" data-bs-toggle="tab" data-bs-target="#topik" type="button" role="tab">
              <i class="ti-book tab-icon"></i> Topik Materi
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="pdf-tab" data-bs-toggle="tab" data-bs-target="#pdf" type="button" role="tab">
              <i class="ti-file tab-icon"></i> Modul PDF (RAG)
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="quiz-tab" data-bs-toggle="tab" data-bs-target="#quiz" type="button" role="tab">
              <i class="ti-write tab-icon"></i> Soal Kuis
            </button>
          </li>
        </ul>

        <div class="tab-content mt-3" id="detailTabContent">
          <!-- Tab 1: Topik Materi -->
          <div class="tab-pane fade show active" id="topik" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="mb-0">Daftar Topik</h5>
              <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTopik">
                <i class="ti-plus"></i> Tambah Topik
              </button>
            </div>

            @forelse($pertemuan->topikMateris as $topik)
              <div class="topik-item">
                <div class="d-flex justify-content-between">
                  <div>
                    <strong>#{{ $topik->urutan ?? '-' }} {{ $topik->judul }}</strong>
                    @if($topik->file_materi)
                      <a href="{{ asset('storage/'.$topik->file_materi) }}" target="_blank" class="badge bg-info ms-2">
                        <i class="ti-download"></i> File
                      </a>
                    @endif
                  </div>
                  <div>
                    <a href="{{ route('admin.topik.edit', $topik->id) }}" class="btn btn-outline-secondary btn-sm"><i class="ti-pencil"></i></a>
                    <form action="{{ route('admin.topik.destroy', $topik->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus topik ini?')">
                      @csrf @method('DELETE')
                      <button class="btn btn-outline-danger btn-sm"><i class="ti-trash"></i></button>
                    </form>
                  </div>
                </div>
                <p class="text-muted small mt-1 mb-0">{{ Str::limit($topik->isi_materi, 150) }}</p>
              </div>
            @empty
              <div class="text-center py-4 text-muted">Belum ada topik materi. Tambahkan sekarang.</div>
            @endforelse
          </div>

          <!-- Tab 2: Modul PDF -->
          <div class="tab-pane fade" id="pdf" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="mb-0">Modul PDF untuk RAG</h5>
              <form action="{{ route('admin.pdf.upload', $pertemuan->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="input-group input-group-sm">
                  <input type="file" name="file_pdf" accept=".pdf" class="form-control" required>
                  <button class="btn btn-primary" type="submit">
                    <i class="ti-upload"></i> Upload & Index
                  </button>
                </div>
              </form>
            </div>

            <table class="table table-sm">
              <thead>
                <tr>
                  <th>File</th>
                  <th>Status Indexing</th>
                  <th>Tanggal</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                @forelse($pertemuan->modulPdfs as $pdf)
                  <tr>
                    <td>{{ $pdf->file_name }}</td>
                    <td>
                      <span class="status-{{ $pdf->status_indexing }} text-capitalize">
                        {{ $pdf->status_indexing }}
                      </span>
                    </td>
                    <td>{{ $pdf->created_at->format('d M Y H:i') }}</td>
                    <td>
                      <form action="{{ route('admin.pdf.reindex', $pdf->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button class="btn btn-outline-info btn-sm"><i class="ti-reload"></i> Re-index</button>
                      </form>
                      <form action="{{ route('admin.pdf.destroy', $pdf->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus PDF ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm"><i class="ti-trash"></i></button>
                      </form>
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="4" class="text-center text-muted">Belum ada PDF.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <!-- Tab 3: Soal Kuis -->
          <div class="tab-pane fade" id="quiz" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="mb-0">Daftar Soal Kuis</h5>
              <div>
                <button class="btn btn-outline-info btn-sm" onclick="generateSoal()">
                  <i class="ti-wand"></i> Generate by AI
                </button>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalSoal">
                  <i class="ti-plus"></i> Tambah Manual
                </button>
              </div>
            </div>

            @forelse($pertemuan->soalKuis as $soal)
              <div class="card mb-2">
                <div class="card-body py-2">
                  <div class="d-flex justify-content-between">
                    <div>
                      <strong>#{{ $loop->iteration }}. {{ $soal->pertanyaan }}</strong>
                      <span class="badge bg-success ms-2">Kunci: {{ $soal->kunci_jawaban }}</span>
                    </div>
                    <div>
                      <a href="{{ route('admin.quiz.edit', $soal->id) }}" class="btn btn-outline-secondary btn-sm"><i class="ti-pencil"></i></a>
                      <form action="{{ route('admin.quiz.destroy', $soal->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus soal ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm"><i class="ti-trash"></i></button>
                      </form>
                    </div>
                  </div>
                  <small class="text-muted">A. {{ $soal->pilihan_a }} | B. {{ $soal->pilihan_b }} | C. {{ $soal->pilihan_c }} | D. {{ $soal->pilihan_d }}</small>
                </div>
              </div>
            @empty
              <div class="text-center py-4 text-muted">Belum ada soal kuis.</div>
            @endforelse
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- Modal Tambah Topik -->
<div class="modal fade" id="modalTopik" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="{{ route('admin.topik.store', $pertemuan->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Tambah Topik Materi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Judul</label>
            <input type="text" name="judul" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Urutan</label>
            <input type="number" name="urutan" class="form-control" value="{{ $pertemuan->topikMateris->max('urutan') + 1 ?? 1 }}">
          </div>
          <div class="mb-3">
            <label class="form-label">Deskripsi</label>
            <textarea name="deskripsi" class="form-control" rows="2"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Isi Materi</label>
            <textarea name="isi_materi" class="form-control" rows="6" required></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">File Materi (PDF/PPT/DOC)</label>
            <input type="file" name="file_materi" class="form-control" accept=".pdf,.ppt,.pptx,.doc,.docx">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Tambah Soal Manual -->
<div class="modal fade" id="modalSoal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="{{ route('admin.quiz.store', $pertemuan->id) }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Tambah Soal Kuis</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Pertanyaan</label>
            <textarea name="pertanyaan" class="form-control" rows="2" required></textarea>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Pilihan A</label>
              <input type="text" name="pilihan_a" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Pilihan B</label>
              <input type="text" name="pilihan_b" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Pilihan C</label>
              <input type="text" name="pilihan_c" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Pilihan D</label>
              <input type="text" name="pilihan_d" class="form-control" required>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Kunci Jawaban</label>
              <select name="kunci_jawaban" class="form-select" required>
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="C">C</option>
                <option value="D">D</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Penjelasan</label>
              <textarea name="penjelasan" class="form-control" rows="2"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
  var triggerTabList = [].slice.call(document.querySelectorAll('#detailTab button'))
  triggerTabList.forEach(function (triggerEl) {
    var tabTrigger = new bootstrap.Tab(triggerEl)
    triggerEl.addEventListener('click', function (event) {
      event.preventDefault()
      tabTrigger.show()
    })
  })

  function generateSoal() {
    if (!confirm('Generate soal kuis menggunakan AI? Ini akan memproses topik materi pertemuan ini.')) return;
    fetch('{{ route('admin.quiz.generate', $pertemuan->id) }}', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json'
      }
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        alert(data.message || 'Soal berhasil di-generate!');
        location.reload();
      } else {
        alert('Gagal: ' + (data.message || 'Unknown error'));
      }
    })
    .catch(err => {
      alert('Error: ' + err.message);
    });
  }
</script>
@endpush
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
  .pdf-viewer-modal .modal-dialog { max-width: 90vw; }
  .pdf-viewer-modal .modal-body { height: 70vh; }
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
              <i class="ti-book tab-icon"></i> Isi Materi
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
          <!-- Tab 1: Isi Materi -->
          <div class="tab-pane fade show active" id="topik" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="mb-0">Materi Pembelajaran</h5>
              <a href="{{ route('admin.materi.edit', $pertemuan->id) }}" class="btn btn-primary btn-sm">
                <i class="ti-pencil"></i> Edit Isi Materi
              </a>
            </div>

            <div class="p-4 border rounded bg-light" style="white-space: pre-wrap; line-height: 1.6; font-size: 14px; max-height: 500px; overflow-y: auto;">
              @if($pertemuan->isi_materi)
                {{ $pertemuan->isi_materi }}
              @else
                <div class="text-center text-muted py-4">Belum ada isi materi pembelajaran. Klik tombol <strong>Edit Isi Materi</strong> untuk menambahkan materi.</div>
              @endif
            </div>
          </div>

          <!-- Tab 2: Modul PDF -->
          <div class="tab-pane fade" id="pdf" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="mb-0">Modul PDF untuk RAG</h5>
              <form id="uploadPdfForm" action="{{ route('admin.pdf.upload', $pertemuan->id) }}" method="POST" enctype="multipart/form-data">
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
                      <button class="btn btn-outline-primary btn-sm" onclick="viewPdf('{{ asset('storage/modul_pdf/'.$pdf->file_name) }}', '{{ $pdf->file_name }}')">
                        <i class="ti-eye"></i> View
                      </button>
                      <form action="{{ route('admin.pdf.reindex', $pdf->id) }}" method="POST" class="d-inline reindex-form">
                        @csrf
                        <button class="btn btn-outline-info btn-sm"><i class="ti-reload"></i> Re-index</button>
                      </form>
                      <button class="btn btn-outline-danger btn-sm" onclick="deletePdf({{ $pdf->id }}, '{{ $pdf->file_name }}', {{ $pertemuan->id }})">
                        <i class="ti-trash"></i>
                      </button>
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
                      <button class="btn btn-outline-danger btn-sm" onclick="deleteSoal({{ $soal->id }}, '{{ addslashes(Str::limit($soal->pertanyaan, 50)) }}')">
                        <i class="ti-trash"></i>
                      </button>
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


<!-- Modal PDF Viewer -->
<div class="modal fade pdf-viewer-modal" id="modalPdfViewer" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pdfTitle">PDF Viewer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <iframe id="pdfFrame" src="" frameborder="0" width="100%" height="100%"></iframe>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
        <a id="pdfDownload" href="" class="btn btn-primary" download>
          <i class="ti-download"></i> Download
        </a>
      </div>
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

  // PDF Viewer Functions
  function viewPdf(url, title) {
    document.getElementById('pdfTitle').textContent = title;
    document.getElementById('pdfFrame').src = url;
    document.getElementById('pdfDownload').href = url;
    var modal = new bootstrap.Modal(document.getElementById('modalPdfViewer'));
    modal.show();
  }

  // Delete PDF with SweetAlert
  function deletePdf(id, fileName, pertemuanId) {
    Swal.fire({
      title: 'Hapus PDF?',
      text: 'Apakah Anda yakin ingin menghapus file "' + fileName + '"?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Ya, hapus!',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        fetch('{{ url('admin/pdf') }}/' + id, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          }
        })
        .then(res => res.json())
        .then(data => {
          if (data.success || data.redirect) {
            Swal.fire('Terhapus!', 'PDF berhasil dihapus.', 'success');
            location.reload();
          } else {
            Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus.', 'error');
          }
        })
        .catch(err => {
          Swal.fire('Error!', 'Terjadi kesalahan: ' + err.message, 'error');
        });
      }
    });
  }

  // Delete Topik with SweetAlert
  function deleteTopik(id, judul) {
    Swal.fire({
      title: 'Hapus Topik?',
      text: 'Apakah Anda yakin ingin menghapus topik "' + judul + '"?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Ya, hapus!',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        fetch('{{ url('admin/topik') }}/' + id, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          }
        })
        .then(res => res.json())
        .then(data => {
          if (data.success || data.redirect) {
            Swal.fire('Terhapus!', 'Topik berhasil dihapus.', 'success');
            location.reload();
          } else {
            Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus.', 'error');
          }
        })
        .catch(err => {
          Swal.fire('Error!', 'Terjadi kesalahan: ' + err.message, 'error');
        });
      }
    });
  }

  // Delete Soal with SweetAlert
  function deleteSoal(id, pertanyaan) {
    Swal.fire({
      title: 'Hapus Soal?',
      text: 'Apakah Anda yakin ingin menghapus soal "' + pertanyaan + '"?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Ya, hapus!',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        fetch('{{ url('admin/quiz') }}/' + id, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          }
        })
        .then(res => res.json())
        .then(data => {
          if (data.success || data.redirect) {
            Swal.fire('Terhapus!', 'Soal berhasil dihapus.', 'success');
            location.reload();
          } else {
            Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus.', 'error');
          }
        })
        .catch(err => {
          Swal.fire('Error!', 'Terjadi kesalahan: ' + err.message, 'error');
        });
      }
    });
  }

  // Generate Soal with SweetAlert
  function generateSoal() {
    Swal.fire({
      title: 'Generate Soal AI',
      html: `
        <p class="mb-3">AI akan membuat soal pilihan ganda dari modul yang sudah di-index.</p>
        <div class="d-flex align-items-center gap-2">
          <label class="form-label mb-0">Jumlah Soal:</label>
          <input id="swal-jumlah-soal" type="number" class="form-control" value="5" min="1" max="20" style="width:80px;display:inline-block;">
        </div>
      `,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Generate!',
      cancelButtonText: 'Batal',
      preConfirm: () => {
        const jumlah = document.getElementById('swal-jumlah-soal').value;
        if (!jumlah || jumlah < 1 || jumlah > 20) {
          Swal.showValidationMessage('Jumlah soal harus 1-20');
          return false;
        }
        return parseInt(jumlah);
      }
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire({
          title: 'Memproses...',
          text: 'AI sedang generate soal, mohon tunggu...',
          icon: 'info',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        fetch('{{ route('admin.quiz.generate', $pertemuan->id) }}', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ jumlah_soal: result.value })
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            Swal.fire('Sukses!', data.message || 'Soal berhasil di-generate!', 'success');
            location.reload();
          } else {
            Swal.fire('Gagal!', data.message || 'Unknown error', 'error');
          }
        })
        .catch(err => {
          Swal.fire('Error!', err.message, 'error');
        });
      }
    });
  }

  // Handle PDF Upload Form Loading
  const uploadPdfForm = document.getElementById('uploadPdfForm');
  if (uploadPdfForm) {
    uploadPdfForm.addEventListener('submit', function(e) {
      Swal.fire({
        title: 'Mengunggah & Mengindeks PDF...',
        text: 'AI sedang mengekstrak teks modul dan membuat vektor embedding. Harap tunggu...',
        icon: 'info',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });
    });
  }

  // Handle Re-index Form Loading
  document.querySelectorAll('.reindex-form').forEach(form => {
    form.addEventListener('submit', function(e) {
      Swal.fire({
        title: 'Membangun Ulang Indeks (Re-indexing)...',
        text: 'Server sedang memperbarui representasi vektor untuk pencarian RAG yang akurat.',
        icon: 'info',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });
    });
  });
</script>
@endpush

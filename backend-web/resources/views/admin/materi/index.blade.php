<![CDATA[@extends('layouts.master')

@section('title', 'Pertemuan - NetLabs Admin')

@section('content')
<div class="nl-page-header d-flex justify-content-between align-items-center">
  <div>
    <h3>Manajemen Pertemuan</h3>
    <p>Kelola semua pertemuan materi pembelajaran.</p>
  </div>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPertemuan">
    <i class="ti-plus me-1"></i> Tambah Pertemuan
  </button>
</div>

@if(session('success'))
  <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
    <i class="ti-check-box"></i> {{ session('success') }}
  </div>
@endif

<ul class="nav nav-tabs" id="semesterTab" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="semester1-tab" data-bs-toggle="tab" data-bs-target="#semester1" type="button" role="tab">
      <i class="ti-calendar me-1"></i> Semester 1
      <span class="badge bg-secondary ms-1">{{ $pertemuanSemester1->count() }}</span>
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="semester2-tab" data-bs-toggle="tab" data-bs-target="#semester2" type="button" role="tab">
      <i class="ti-calendar me-1"></i> Semester 2
      <span class="badge bg-secondary ms-1">{{ $pertemuanSemester2->count() }}</span>
    </button>
  </li>
</ul>

<div class="tab-content mt-3" id="semesterTabContent">
  <div class="tab-pane fade show active" id="semester1" role="tabpanel">
    @if($pertemuanSemester1->isEmpty())
      <div class="card">
        <div class="card-body text-center py-5 text-muted">
          <i class="ti-inbox" style="font-size:48px;opacity:0.3;display:block;margin-bottom:8px;"></i>
          Belum ada pertemuan di Semester 1.
        </div>
      </div>
    @else
      <div class="row">
        @foreach($pertemuanSemester1 as $p)
          @include('admin.materi.partials.pertemuan-card', ['p' => $p])
        @endforeach
      </div>
    @endif
  </div>
  <div class="tab-pane fade" id="semester2" role="tabpanel">
    @if($pertemuanSemester2->isEmpty())
      <div class="card">
        <div class="card-body text-center py-5 text-muted">
          <i class="ti-inbox" style="font-size:48px;opacity:0.3;display:block;margin-bottom:8px;"></i>
          Belum ada pertemuan di Semester 2.
        </div>
      </div>
    @else
      <div class="row">
        @foreach($pertemuanSemester2 as $p)
          @include('admin.materi.partials.pertemuan-card', ['p' => $p])
        @endforeach
      </div>
    @endif
  </div>
</div>
@endsection

<!-- Modal Tambah Pertemuan -->
<div class="modal fade" id="modalPertemuan" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('admin.materi.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title"><i class="ti-plus me-1"></i> Tambah Pertemuan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nomor Urut <span class="text-danger">*</span></label>
              <input type="number" name="nomor_urut" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Semester <span class="text-danger">*</span></label>
              <select name="semester" class="form-select" required>
                <option value="">-- Pilih --</option>
                <option value="1">Semester 1</option>
                <option value="2">Semester 2</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Judul <span class="text-danger">*</span></label>
              <input type="text" name="judul" class="form-control" required>
            </div>
            <div class="col-12">
              <label class="form-label">Deskripsi</label>
              <textarea name="deskripsi" class="form-control" rows="3"></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Warna Tema</label>
              <input type="color" name="warna_tema" class="form-control form-control-color" value="#4F6AF5">
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

@push('scripts')
<script>
  function deletePertemuan(id, judul) {
    Swal.fire({
      title: 'Hapus Pertemuan?',
      text: 'Apakah Anda yakin ingin menghapus pertemuan "' + judul + '"?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#EF4444',
      cancelButtonColor: '#8B8FA3',
      confirmButtonText: 'Ya, hapus!',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        fetch('{{ url('admin/materi') }}/' + id, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          }
        })
        .then(res => res.json())
        .then(data => {
          if (data.success || data.redirect) {
            Swal.fire('Terhapus!', 'Pertemuan berhasil dihapus.', 'success');
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
</script>
@endpush
]]>
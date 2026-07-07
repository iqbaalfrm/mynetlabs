@extends('layouts.master')

@section('title', 'Manajemen Kelas - NetLabs Admin')

@section('content')
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="card-title mb-0">Manajemen Kelas</h4>
          <button class="btn btn-primary btn-sm" onclick="openModalCreate()">
            <i class="ti-plus"></i> Tambah Kelas
          </button>
        </div>

        @if(session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
          <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>#</th>
                <th>Nama Kelas</th>
                <th>Wali Kelas</th>
                <th>Jumlah Siswa</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($kelasList as $kelas)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td><strong>{{ $kelas->nama_kelas }}</strong></td>
                  <td>{{ $kelas->waliKelas->nama ?? '-' }}</td>
                  <td>
                    <span class="badge bg-info">{{ $kelas->siswa_count }} siswa</span>
                  </td>
                  <td>
                    <button class="btn btn-outline-secondary btn-sm" onclick="openModalEdit({{ $kelas->id }}, '{{ addslashes($kelas->nama_kelas) }}', {{ $kelas->wali_kelas_id ?? 'null' }})">
                      <i class="ti-pencil"></i>
                    </button>
                    <button class="btn btn-outline-danger btn-sm" onclick="deleteKelas({{ $kelas->id }}, '{{ addslashes($kelas->nama_kelas) }}')">
                      <i class="ti-trash"></i>
                    </button>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center text-muted py-3">Belum ada kelas.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Create/Edit Kelas -->
<div class="modal fade" id="modalKelas" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formKelas" method="POST" action="">
        @csrf
        <input type="hidden" name="_method" id="formMethod" value="">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Tambah Kelas</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nama Kelas</label>
            <input type="text" name="nama_kelas" id="inputNamaKelas" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Wali Kelas (Guru)</label>
            <select name="wali_kelas_id" id="inputWaliKelas" class="form-select">
              <option value="">-- Pilih Wali Kelas --</option>
              @foreach($guruList as $guru)
                <option value="{{ $guru->id }}">{{ $guru->nama }} ({{ $guru->username }})</option>
              @endforeach
            </select>
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
  var modalKelas = new bootstrap.Modal(document.getElementById('modalKelas'));

  function openModalCreate() {
    document.getElementById('modalTitle').textContent = 'Tambah Kelas';
    document.getElementById('formKelas').action = '{{ route('admin.kelas.store') }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('inputNamaKelas').value = '';
    document.getElementById('inputWaliKelas').value = '';
    modalKelas.show();
  }

  function openModalEdit(id, nama, waliKelasId) {
    document.getElementById('modalTitle').textContent = 'Edit Kelas';
    document.getElementById('formKelas').action = '{{ url('admin/kelas') }}/' + id;
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('inputNamaKelas').value = nama;
    document.getElementById('inputWaliKelas').value = waliKelasId || '';
    modalKelas.show();
  }

  function deleteKelas(id, nama) {
    Swal.fire({
      title: 'Hapus Kelas?',
      text: 'Apakah Anda yakin ingin menghapus kelas "' + nama + '"?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Ya, hapus!',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        fetch('{{ url('admin/kelas') }}/' + id, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          }
        })
        .then(res => res.json())
        .then(data => {
          if (data.success || data.redirect) {
            Swal.fire('Terhapus!', 'Kelas berhasil dihapus.', 'success');
            location.reload();
          } else {
            Swal.fire('Gagal!', data.message || 'Terjadi kesalahan saat menghapus.', 'error');
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
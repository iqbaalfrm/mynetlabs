@extends('layouts.master')

@section('title', 'Kelola Siswa - NetLabs Admin')

@section('content')
<div class="row">
  <div class="col-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h4 class="card-title mb-1" style="font-weight: 700;">Manajemen Akun Siswa</h4>
            <p class="text-muted mb-0" style="font-size: 13px;">Kelola pendaftaran siswa, kelas, dan status keaktifan akun.</p>
          </div>
          <a href="{{ route('admin.siswa.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
            <i class="ti-plus" style="font-size: 12px;"></i> Tambah Siswa
          </a>
        </div>

        @if(session('success'))
          <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif

        <!-- Filter & Search Section -->
        <form method="GET" action="{{ route('admin.siswa.index') }}" class="row g-2 mb-4">
          <div class="col-md-4 col-sm-6">
            <div class="input-group">
              <span class="input-group-text bg-transparent border-end-0"><i class="ti-search text-muted"></i></span>
              <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama atau NIS..." value="{{ request('search') }}">
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <select name="kelas_id" class="form-select">
              <option value="">Semua Kelas</option>
              @foreach($kelasList as $kelas)
                <option value="{{ $kelas->id }}" {{ request('kelas_id') == $kelas->id ? 'selected' : '' }}>{{ $kelas->nama_kelas }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-dark">Filter</button>
            <a href="{{ route('admin.siswa.index') }}" class="btn btn-outline-secondary">Reset</a>
          </div>
        </form>

        <!-- Table Data Siswa -->
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr class="bg-light">
                <th width="60">No</th>
                <th>Nama Lengkap</th>
                <th>Username (NIS)</th>
                <th>Kelas</th>
                <th>Status</th>
                <th width="180">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($siswa as $item)
                <tr>
                  <td>{{ $loop->iteration + $siswa->firstItem() - 1 }}</td>
                  <td>
                    <div class="d-flex align-items-center">
                      @if($item->foto_profil)
                        <img src="{{ $item->foto_profil_url }}" class="rounded-circle me-2" width="32" height="32" style="object-fit: cover;">
                      @else
                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2 text-primary" style="width: 32px; height: 32px; font-weight: 700; font-size: 12px;">
                          {{ strtoupper(substr($item->nama, 0, 2)) }}
                        </div>
                      @endif
                      <div>
                        <span style="font-weight: 600; color: #1F2937;">{{ $item->nama }}</span>
                      </div>
                    </div>
                  </td>
                  <td><code class="px-2 py-1 bg-light text-dark rounded">{{ $item->username }}</code></td>
                  <td>{{ $item->kelasRelation->nama_kelas ?? '-' }}</td>
                  <td>
                    <form action="{{ route('admin.siswa.toggle-status', $item->id) }}" method="POST" class="d-inline">
                      @csrf
                      @method('PATCH')
                      @if($item->status === 'aktif')
                        <button type="submit" class="badge bg-success border-0 text-white" style="cursor: pointer;" title="Klik untuk nonaktifkan akun">
                          <i class="ti-check me-1"></i> Aktif
                        </button>
                      @else
                        <button type="submit" class="badge bg-danger border-0 text-white" style="cursor: pointer;" title="Klik untuk aktifkan akun">
                          <i class="ti-power-off me-1"></i> Nonaktif
                        </button>
                      @endif
                    </form>
                  </td>
                  <td>
                    <div class="d-flex gap-2">
                      <a href="{{ route('admin.siswa.edit', $item->id) }}" class="btn btn-sm btn-outline-secondary" title="Edit Data">
                        <i class="ti-pencil"></i>
                      </a>
                      <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete({{ $item->id }}, '{{ addslashes($item->nama) }}')" title="Hapus Siswa">
                        <i class="ti-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center py-5 text-muted">
                    <i class="ti-face-sad d-block mb-2" style="font-size: 32px;"></i>
                    Belum ada data siswa terdaftar.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <!-- Pagination Section -->
        <div class="d-flex justify-content-between align-items-center mt-4">
          <p class="text-muted mb-0" style="font-size: 13px;">
            Menampilkan {{ $siswa->firstItem() ?? 0 }} - {{ $siswa->lastItem() ?? 0 }} dari {{ $siswa->total() }} siswa.
          </p>
          <div>
            {{ $siswa->appends(request()->query())->links() }}
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- Deletion Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteModalLabel" style="font-weight: 700;">Konfirmasi Penghapusan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0">Apakah Anda yakin ingin menghapus akun siswa bernama <strong id="deleteSiswaName"></strong> secara permanen?</p>
        <small class="text-danger d-block mt-2"><i class="ti-info-alt me-1"></i> Tindakan ini tidak dapat dibatalkan.</small>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <form id="deleteForm" method="POST" class="d-inline">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger">Hapus Permanen</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  function confirmDelete(id, name) {
    document.getElementById('deleteSiswaName').innerText = name;
    document.getElementById('deleteForm').action = "{{ url('admin/siswa') }}/" + id;
    var myModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    myModal.show();
  }
</script>
@endsection

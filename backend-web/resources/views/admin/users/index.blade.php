@extends('layouts.master')

@section('title', 'Kelola Users - NetLabs Admin')

@section('content')
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="card-title mb-0">Kelola Users</h4>
          <button class="btn btn-primary btn-sm" onclick="openModalCreate()">
            <i class="ti-plus"></i> Tambah User
          </button>
        </div>

        @if(session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Filter -->
        <form method="GET" class="row mb-3">
          <div class="col-md-3">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama/username..." value="{{ request('search') }}">
          </div>
          <div class="col-md-2">
            <select name="role" class="form-select form-select-sm">
              <option value="">Semua Role</option>
              <option value="guru" {{ request('role') == 'guru' ? 'selected' : '' }}>Guru</option>
              <option value="siswa" {{ request('role') == 'siswa' ? 'selected' : '' }}>Siswa</option>
            </select>
          </div>
          <div class="col-md-2">
            <select name="kelas_id" class="form-select form-select-sm">
              <option value="">Semua Kelas</option>
              @foreach($kelasList as $kelas)
                <option value="{{ $kelas->id }}" {{ request('kelas_id') == $kelas->id ? 'selected' : '' }}>{{ $kelas->nama_kelas }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-sm btn-outline-secondary">Filter</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-danger">Reset</a>
          </div>
        </form>

        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>#</th>
                <th>Username</th>
                <th>Nama</th>
                <th>Role</th>
                <th>Kelas</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($users as $user)
                <tr>
                  <td>{{ $loop->iteration + $users->firstItem() - 1 }}</td>
                  <td>
                    @if($user->foto_profil)
                      <img src="{{ $user->foto_profil_url }}" class="rounded-circle me-1" width="28" height="28" style="object-fit:cover;">
                    @endif
                    {{ $user->username }}
                  </td>
                  <td>{{ $user->nama }}</td>
                  <td>
                    <span class="badge {{ $user->role == 'guru' ? 'bg-primary' : 'bg-info' }}">
                      {{ ucfirst($user->role) }}
                    </span>
                  </td>
                  <td>{{ $user->kelasRelation->nama_kelas ?? '-' }}</td>
                  <td>
                     <button class="btn btn-outline-secondary btn-sm"
                       onclick="openModalEdit({{ $user->id }}, '{{ addslashes($user->username) }}', '{{ addslashes($user->nama) }}', '{{ $user->role }}', {{ $user->kelas_id ?? 'null' }})">
                       <i class="ti-pencil"></i>
                     </button>
                     <button class="btn btn-outline-danger btn-sm" onclick="deleteUser({{ $user->id }}, '{{ addslashes($user->nama) }}')">
                       <i class="ti-trash"></i>
                     </button>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center text-muted py-3">Belum ada user.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
         </div>

         <div class="mt-3">
           {{ $users->appends(request()->query())->links() }}
         </div>
       </div>
     </div>
   </div>
 </div>

<!-- Modal Create/Edit User -->
<div class="modal fade" id="modalUser" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formUser" method="POST" action="" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="_method" id="formMethod" value="">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Tambah User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Username <span class="text-danger">*</span></label>
            <input type="text" name="username" id="inputUsername" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Nama <span class="text-danger">*</span></label>
            <input type="text" name="nama" id="inputNama" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label" id="labelPassword">Password <span class="text-danger">*</span></label>
            <input type="password" name="password" id="inputPassword" class="form-control">
            <small class="text-muted" id="passwordHint" style="display:none;">Kosongkan jika tidak ingin diubah</small>
          </div>
          <div class="mb-3">
            <label class="form-label">Role <span class="text-danger">*</span></label>
            <select name="role" id="inputRole" class="form-select" required>
              <option value="">Pilih Role</option>
              <option value="guru">Guru</option>
              <option value="siswa">Siswa</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Kelas</label>
            <select name="kelas_id" id="inputKelas" class="form-select">
              <option value="">Tanpa Kelas</option>
              @foreach($kelasList as $kelas)
                <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Foto Profil</label>
            <input type="file" name="foto_profil" class="form-control" accept="image/*">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="btnSubmit">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  var modalUser = new bootstrap.Modal(document.getElementById('modalUser'));

  function openModalCreate() {
    document.getElementById('modalTitle').textContent = 'Tambah User';
    document.getElementById('formUser').action = '{{ route('admin.users.store') }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('inputUsername').value = '';
    document.getElementById('inputNama').value = '';
    document.getElementById('inputPassword').value = '';
    document.getElementById('inputPassword').required = true;
    document.getElementById('labelPassword').innerHTML = 'Password <span class="text-danger">*</span>';
    document.getElementById('passwordHint').style.display = 'none';
    document.getElementById('inputRole').value = '';
    document.getElementById('inputKelas').value = '';
    document.getElementById('btnSubmit').textContent = 'Simpan';
    modalUser.show();
  }

  function openModalEdit(id, username, nama, role, kelasId) {
    document.getElementById('modalTitle').textContent = 'Edit User';
    document.getElementById('formUser').action = '{{ url('admin/users') }}/' + id;
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('inputUsername').value = username;
    document.getElementById('inputNama').value = nama;
    document.getElementById('inputPassword').value = '';
    document.getElementById('inputPassword').required = false;
    document.getElementById('labelPassword').innerHTML = 'Password';
    document.getElementById('passwordHint').style.display = 'block';
    document.getElementById('inputRole').value = role;
    document.getElementById('inputKelas').value = kelasId || '';
    document.getElementById('btnSubmit').textContent = 'Update';
    modalUser.show();
  }

  // Delete User with SweetAlert
  function deleteUser(id, nama) {
    Swal.fire({
      title: 'Hapus User?',
      text: 'Apakah Anda yakin ingin menghapus user "' + nama + '"?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Ya, hapus!',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        fetch('{{ url('admin/users') }}/' + id, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          }
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            Swal.fire('Terhapus!', 'User berhasil dihapus.', 'success');
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
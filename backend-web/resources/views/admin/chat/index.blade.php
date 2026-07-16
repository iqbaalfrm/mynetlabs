@extends('layouts.master')

@section('title', 'Monitoring Chat AI - NetLabs Admin')

@section('content')
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title mb-3">Monitoring Chat AI</h4>

        @if(session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="GET" class="row g-2 mb-3">
          <div class="col-md-3">
            <select name="siswa_id" class="form-select">
              <option value="">Semua Siswa</option>
              @foreach($siswaList as $siswa)
                <option value="{{ $siswa->id }}" @if(request('siswa_id') == $siswa->id) selected @endif>{{ $siswa->nama }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <select name="pertemuan_id" class="form-select">
              <option value="">Semua Pertemuan</option>
              @foreach($pertemuanList as $pertemuan)
                <option value="{{ $pertemuan->id }}" @if(request('pertemuan_id') == $pertemuan->id) selected @endif>P{{ $pertemuan->nomor_urut }} - {{ $pertemuan->judul }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <select name="sender" class="form-select">
              <option value="">Semua Sender</option>
              <option value="siswa" @if(request('sender') == 'siswa') selected @endif>Siswa</option>
              <option value="ai" @if(request('sender') == 'ai') selected @endif>AI</option>
            </select>
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
          </div>
          <div class="col-md-2">
            <a href="{{ route('admin.chat.index') }}" class="btn btn-light w-100">Reset</a>
          </div>
        </form>

        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>#</th>
                <th>Waktu</th>
                <th>Siswa</th>
                <th>Pertemuan</th>
                <th>Sender</th>
                <th>Pesan</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($chatList as $chat)
                <tr>
                  <td>{{ $chatList->firstItem() + $loop->index }}</td>
                  <td>{{ $chat->created_at->format('d/m/Y H:i') }}</td>
                  <td>{{ $chat->siswa->nama ?? '-' }}</td>
                  <td>{{ $chat->pertemuan ? 'P' . $chat->pertemuan->nomor_urut : '-' }}</td>
                  <td>
                    @if($chat->sender == 'siswa')
                      <span class="badge bg-primary">Siswa</span>
                    @else
                      <span class="badge bg-success">AI</span>
                    @endif
                  </td>
                  <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    {{ Str::limit($chat->pesan, 80) }}
                  </td>
                  <td>
                    <button class="btn btn-outline-info btn-sm" onclick="viewChat('{{ addslashes($chat->siswa->nama ?? 'Unknown') }}', '{{ $chat->sender }}', {{ json_encode($chat->pesan) }}, '{{ $chat->sumber_referensi ?? '' }}', '{{ $chat->created_at->format('d/m/Y H:i') }}')">
                      <i class="ti-eye"></i>
                    </button>
                    <button class="btn btn-outline-danger btn-sm" onclick="deleteChat({{ $chat->id }})">
                      <i class="ti-trash"></i>
                    </button>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center text-muted py-3">Tidak ada riwayat chat.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
          <div class="text-muted small">
            Menampilkan {{ $chatList->firstItem() ?? 0 }} - {{ $chatList->lastItem() ?? 0 }} dari {{ $chatList->total() }} data
          </div>
          @if($chatList->hasPages())
            <nav>
              <ul class="pagination mb-0">
                {{-- Tombol Previous --}}
                @if($chatList->onFirstPage())
                  <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                @else
                  <li class="page-item"><a class="page-link" href="{{ $chatList->previousPageUrl() }}">&laquo;</a></li>
                @endif

                {{-- Nomor Halaman --}}
                @foreach($chatList->getUrlRange(max(1, $chatList->currentPage() - 2), min($chatList->lastPage(), $chatList->currentPage() + 2)) as $page => $url)
                  <li class="page-item {{ $page == $chatList->currentPage() ? 'active' : '' }}">
                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                  </li>
                @endforeach

                {{-- Tombol Next --}}
                @if($chatList->hasMorePages())
                  <li class="page-item"><a class="page-link" href="{{ $chatList->nextPageUrl() }}">&raquo;</a></li>
                @else
                  <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                @endif
              </ul>
            </nav>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalViewChat" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detail Chat</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered">
          <tr><th style="width: 120px;">Siswa</th><td id="detailSiswa"></td></tr>
          <tr><th>Sender</th><td id="detailSender"></td></tr>
          <tr><th>Waktu</th><td id="detailWaktu"></td></tr>
          <tr><th>Sumber Referensi</th><td id="detailReferensi"></td></tr>
          <tr><th>Pesan</th><td id="detailPesan" style="white-space: pre-wrap;"></td></tr>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  var modalViewChat = new bootstrap.Modal(document.getElementById('modalViewChat'));

  function viewChat(siswa, sender, pesan, referensi, waktu) {
    document.getElementById('detailSiswa').textContent = siswa;
    document.getElementById('detailSender').innerHTML = sender === 'siswa'
      ? '<span class="badge bg-primary">Siswa</span>'
      : '<span class="badge bg-success">AI</span>';
    document.getElementById('detailWaktu').textContent = waktu;
    document.getElementById('detailReferensi').textContent = referensi || '-';
    document.getElementById('detailPesan').textContent = pesan;
    modalViewChat.show();
  }

  function deleteChat(id) {
    Swal.fire({
      title: 'Hapus Pesan?',
      text: 'Pesan chat ini akan dihapus permanen.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Ya, hapus!',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        fetch('{{ url("admin/chat") }}/' + id, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          }
        })
        .then(res => res.json())
        .then(data => {
          if (data.success || data.redirect) {
            Swal.fire('Terhapus!', 'Pesan chat berhasil dihapus.', 'success');
            location.reload();
          } else {
            Swal.fire('Gagal!', data.message || 'Terjadi kesalahan.', 'error');
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
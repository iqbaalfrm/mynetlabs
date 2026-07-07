<div class="col-md-6 col-lg-4 mb-3">
  <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid {{ $p->warna_tema ?? '#3B82F6' }} !important;">
    <div class="card-body">
      <span class="badge bg-primary mb-2">Pertemuan {{ $p->nomor_urut }}</span>
      <h6 class="card-title">{{ $p->judul }}</h6>
      @if($p->deskripsi)
        <p class="text-muted small">{{ Str::limit($p->deskripsi, 80) }}</p>
      @endif
      <div class="d-flex justify-content-between align-items-center mt-2">
        <a href="{{ route('admin.materi.show', $p->id) }}" class="btn btn-outline-primary btn-sm">
          <i class="ti-eye"></i> Detail
        </a>
         <div>
           <a href="{{ route('admin.materi.edit', $p->id) }}" class="btn btn-outline-secondary btn-sm">
             <i class="ti-pencil"></i>
           </a>
           <button class="btn btn-outline-danger btn-sm" onclick="deletePertemuan({{ $p->id }}, '{{ addslashes($p->judul) }}')">
             <i class="ti-trash"></i>
           </button>
         </div>
      </div>
    </div>
  </div>
</div>
<![CDATA[@extends('layouts.master')

@section('title', 'Dashboard - NetLabs Admin')

@section('content')
{{-- Page Header --}}
<div class="nl-page-header">
  <h3>Selamat datang, {{ Auth::user()->nama }} 👋</h3>
  <p>Ringkasan aktivitas dan statistik NetLabs hari ini.</p>
</div>

{{-- 4 Stat Cards --}}
<div class="row">
  <div class="col-md-6 col-xl-3 grid-margin">
    <div class="nl-stat-card">
      <div class="nl-stat-icon primary">
        <i class="ti-user"></i>
      </div>
      <div>
        <p class="nl-stat-label">Total Siswa</p>
        <p class="nl-stat-value">{{ $totalSiswa }}</p>
      </div>
    </div>
  </div>
  <div class="col-md-6 col-xl-3 grid-margin">
    <div class="nl-stat-card">
      <div class="nl-stat-icon secondary">
        <i class="ti-briefcase"></i>
      </div>
      <div>
        <p class="nl-stat-label">Total Guru</p>
        <p class="nl-stat-value">{{ $totalGuru }}</p>
      </div>
    </div>
  </div>
  <div class="col-md-6 col-xl-3 grid-margin">
    <div class="nl-stat-card">
      <div class="nl-stat-icon success">
        <i class="ti-book"></i>
      </div>
      <div>
        <p class="nl-stat-label">Total Materi</p>
        <p class="nl-stat-value">{{ $totalMateri }}</p>
      </div>
    </div>
  </div>
  <div class="col-md-6 col-xl-3 grid-margin">
    <div class="nl-stat-card">
      <div class="nl-stat-icon warning">
        <i class="ti-calendar"></i>
      </div>
      <div>
        <p class="nl-stat-label">Total Pertemuan</p>
        <p class="nl-stat-value">{{ $totalPertemuan }}</p>
      </div>
    </div>
  </div>
</div>

{{-- Grafik + Aktivitas Terbaru --}}
<div class="row">
  {{-- Statistik Per Pertemuan (Tabel Aktivitas) --}}
  <div class="col-lg-8 grid-margin">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="card-title mb-0">Statistik Per Pertemuan</h4>
          <a href="{{ route('admin.materi.index') }}" class="btn btn-sm btn-outline-secondary">Lihat Semua</a>
        </div>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>#</th>
                <th>Pertemuan</th>
                <th>Semester</th>
                <th>Topik</th>
                <th>Soal Kuis</th>
              </tr>
            </thead>
            <tbody>
              @forelse($statistikPertemuan as $p)
              <tr>
                <td><span class="badge bg-secondary">{{ $p->nomor_urut }}</span></td>
                <td><strong>{{ $p->judul }}</strong></td>
                <td><span class="badge-outline-info badge">Sem {{ $p->semester }}</span></td>
                <td>{{ $p->topik_materis_count }}</td>
                <td><span class="badge bg-success">{{ $p->soal_kuis_count }}</span></td>
              </tr>
              @empty
              <tr><td colspan="5" class="text-center text-muted py-4">Belum ada data pertemuan</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  {{-- Top 5 Siswa --}}
  <div class="col-lg-4 grid-margin">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title mb-3">Top 5 Siswa</h4>
        <div class="d-flex flex-column gap-3">
          @forelse($topSiswa as $index => $s)
          <div class="d-flex align-items-center gap-3">
            <div style="width:32px;height:32px;border-radius:8px;background:{{ $index == 0 ? 'var(--nl-warning)' : ($index == 1 ? 'var(--nl-muted)' : ($index == 2 ? '#CD7F32' : 'var(--nl-border)')) }};color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;">
              {{ $index + 1 }}
            </div>
            <div class="flex-grow-1">
              <div style="font-weight:600;font-size:14px;color:var(--nl-text);">{{ $s->nama }}</div>
              <div style="font-size:12px;color:var(--nl-muted);">{{ $s->completed_count }} modul selesai</div>
            </div>
            <span class="badge bg-primary">{{ $s->completed_count }}</span>
          </div>
          @empty
          <div class="text-center text-muted py-4">Belum ada progress siswa</div>
          @endforelse
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Ringkasan Cepat --}}
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title mb-3">Ringkasan Cepat</h4>
        <div class="row text-center">
          <div class="col-6 col-md">
            <h3 class="mb-0" style="color: var(--nl-primary); font-weight: 700;">{{ $totalPertemuan }}</h3>
            <small class="text-muted">Pertemuan</small>
          </div>
          <div class="col-6 col-md">
            <h3 class="mb-0" style="color: var(--nl-success); font-weight: 700;">{{ $totalMateri }}</h3>
            <small class="text-muted">Materi</small>
          </div>
          <div class="col-6 col-md">
            <h3 class="mb-0" style="color: var(--nl-warning); font-weight: 700;">{{ $totalSoal }}</h3>
            <small class="text-muted">Soal Kuis</small>
          </div>
          <div class="col-6 col-md">
            <h3 class="mb-0" style="color: var(--nl-info); font-weight: 700;">{{ $totalSiswa }}</h3>
            <small class="text-muted">Siswa</small>
          </div>
          <div class="col-6 col-md">
            <h3 class="mb-0" style="color: var(--nl-danger); font-weight: 700;">{{ $totalGuru }}</h3>
            <small class="text-muted">Guru</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
]]>
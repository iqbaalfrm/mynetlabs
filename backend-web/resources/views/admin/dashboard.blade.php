@extends('layouts.master')

@section('title', 'Dashboard - NetLabs Admin')

@section('content')
<div class="row">
  <div class="col-md-12 grid-margin">
    <div class="row">
      <div class="col-12 col-xl-8 mb-4 mb-xl-0">
        <h3 class="font-weight-bold">Welcome, {{ Auth::user()->nama }}</h3>
        <h6 class="font-weight-normal mb-0">NetLabs Admin Panel</h6>
      </div>
    </div>
  </div>
</div>

{{-- Stat Cards --}}
<div class="row">
  <div class="col-md-3 grid-margin stretch-card">
    <div class="card tale-bg">
      <div class="card-body">
        <p class="card-title">Total Siswa</p>
        <h1 class="text-white">{{ $totalSiswa }}</h1>
      </div>
    </div>
  </div>
  <div class="col-md-3 grid-margin stretch-card">
    <div class="card bg-primary">
      <div class="card-body">
        <p class="card-title text-white">Total Guru</p>
        <h1 class="text-white">{{ $totalGuru }}</h1>
      </div>
    </div>
  </div>
  <div class="col-md-3 grid-margin stretch-card">
    <div class="card bg-success">
      <div class="card-body">
        <p class="card-title text-white">Total Materi</p>
        <h1 class="text-white">{{ $totalMateri }}</h1>
      </div>
    </div>
  </div>
  <div class="col-md-3 grid-margin stretch-card">
    <div class="card bg-warning">
      <div class="card-body">
        <p class="card-title text-white">Total Pertemuan</p>
        <h1 class="text-white">{{ $totalPertemuan }}</h1>
      </div>
    </div>
  </div>
</div>

{{-- Daftar Pertemuan + Statistik --}}
<div class="row">
  <div class="col-lg-7 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Statistik Per Pertemuan</h4>
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>#</th>
                <th>Pertemuan</th>
                <th>Semester</th>
                <th>Topik Materi</th>
                <th>Soal Kuis</th>
              </tr>
            </thead>
            <tbody>
              @forelse($statistikPertemuan as $p)
              <tr>
                <td>{{ $p->nomor_urut }}</td>
                <td>{{ $p->judul }}</td>
                <td><span class="badge badge-outline-info">Semester {{ $p->semester }}</span></td>
                <td>{{ $p->topik_materis_count }}</td>
                <td>{{ $p->soal_kuis_count }}</td>
              </tr>
              @empty
              <tr><td colspan="5" class="text-center text-muted">Belum ada data pertemuan</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-5 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Top 5 Siswa (Progress)</h4>
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Siswa</th>
                <th>Selesai</th>
              </tr>
            </thead>
            <tbody>
              @forelse($topSiswa as $s)
              <tr>
                <td>{{ $s->nama }}</td>
                <td><span class="badge badge-outline-success">{{ $s->completed_count }}</span></td>
              </tr>
              @empty
              <tr><td colspan="2" class="text-center text-muted">Belum ada progress siswa</td></tr>
              @endforelse
            </tbody>
          </table>
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
        <h4 class="card-title">Ringkasan</h4>
        <div class="d-flex justify-content-around text-center py-3">
          <div>
            <h3 class="text-primary mb-0">{{ $totalPertemuan }}</h3>
            <small class="text-muted">Pertemuan</small>
          </div>
          <div>
            <h3 class="text-success mb-0">{{ $totalMateri }}</h3>
            <small class="text-muted">Materi</small>
          </div>
          <div>
            <h3 class="text-warning mb-0">{{ $totalSoal }}</h3>
            <small class="text-muted">Soal Kuis</small>
          </div>
          <div>
            <h3 class="text-info mb-0">{{ $totalSiswa }}</h3>
            <small class="text-muted">Siswa</small>
          </div>
          <div>
            <h3 class="text-danger mb-0">{{ $totalGuru }}</h3>
            <small class="text-muted">Guru</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

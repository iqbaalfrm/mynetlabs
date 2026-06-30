@extends('layouts.master')

@section('title', 'Pertemuan - NetLabs Admin')

@section('content')
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="card-title mb-0">Manajemen Pertemuan</h4>
          <a href="{{ route('admin.materi.create') }}" class="btn btn-primary btn-sm">
            <i class="ti-plus"></i> Tambah Pertemuan
          </a>
        </div>

        @if(session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <ul class="nav nav-tabs" id="semesterTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="semester1-tab" data-bs-toggle="tab" data-bs-target="#semester1" type="button" role="tab">Semester 1</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="semester2-tab" data-bs-toggle="tab" data-bs-target="#semester2" type="button" role="tab">Semester 2</button>
          </li>
        </ul>

        <div class="tab-content mt-3" id="semesterTabContent">
          <div class="tab-pane fade show active" id="semester1" role="tabpanel">
            @if($pertemuanSemester1->isEmpty())
              <div class="text-center py-4 text-muted">Belum ada pertemuan di Semester 1.</div>
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
              <div class="text-center py-4 text-muted">Belum ada pertemuan di Semester 2.</div>
            @else
              <div class="row">
                @foreach($pertemuanSemester2 as $p)
                  @include('admin.materi.partials.pertemuan-card', ['p' => $p])
                @endforeach
              </div>
            @endif
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // Bootstrap 5 tab trigger
  var triggerTabList = [].slice.call(document.querySelectorAll('#semesterTab button'))
  triggerTabList.forEach(function (triggerEl) {
    var tabTrigger = new bootstrap.Tab(triggerEl)
    triggerEl.addEventListener('click', function (event) {
      event.preventDefault()
      tabTrigger.show()
    })
  })
</script>
@endpush
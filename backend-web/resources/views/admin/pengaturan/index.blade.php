@extends('layouts.master')

@section('title', 'Pengaturan - NetLabs Admin')

@section('content')
<div class="row">
  <div class="col-md-8 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Pengaturan</h4>

        @if(session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0">
              @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form method="POST" action="{{ route('admin.pengaturan.update') }}">
          @csrf
          @method('PUT')

          <h5 class="mt-3 mb-3">Aplikasi</h5>
          <div class="mb-3">
            <label class="form-label">Nama Aplikasi</label>
            <input type="text" name="APP_NAME" class="form-control" value="{{ $settings['APP_NAME'] }}" required>
          </div>

          <h5 class="mt-4 mb-3">AI (Chatbot & Generate Quiz)</h5>
          <div class="mb-3">
            <label class="form-label">AI API URL</label>
            <input type="url" name="AI_API_URL" class="form-control" value="{{ $settings['AI_API_URL'] }}" placeholder="https://ai.example.com/api">
          </div>
          <div class="mb-3">
            <label class="form-label">AI API Key</label>
            <input type="text" name="AI_API_KEY" class="form-control" value="{{ $settings['AI_API_KEY'] }}" placeholder="sk-...">
          </div>

          <h5 class="mt-4 mb-3">Sekolah</h5>
          <div class="mb-3">
            <label class="form-label">Nama Sekolah</label>
            <input type="text" name="SCHOOL_NAME" class="form-control" value="{{ $settings['SCHOOL_NAME'] }}" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Alamat</label>
            <input type="text" name="SCHOOL_ADDRESS" class="form-control" value="{{ $settings['SCHOOL_ADDRESS'] }}">
          </div>
          <div class="mb-3">
            <label class="form-label">Telepon</label>
            <input type="text" name="SCHOOL_PHONE" class="form-control" value="{{ $settings['SCHOOL_PHONE'] }}">
          </div>
          <h5 class="mt-4 mb-3">Tautan Kuesioner (Google Form)</h5>
          <div class="mb-3">
            <label class="form-label">Google Form Redirect URL</label>
            <input type="url" name="GOOGLE_FORM_URL" class="form-control" value="{{ $settings['GOOGLE_FORM_URL'] ?? '' }}" placeholder="https://forms.gle/...">
            <small class="text-muted">Siswa dapat mengakses tautan pengisian kuesioner Anda langsung melalui domain: <strong>https://netlabs.web.id/kuesioner</strong></small>
          </div>

          <button type="submit" class="btn btn-primary mt-3">Simpan Pengaturan</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login - NetLabs</title>
  <link rel="stylesheet" href="{{ asset('assets/skydash/dist/assets/vendors/feather/feather.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/skydash/dist/assets/vendors/ti-icons/css/themify-icons.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/skydash/dist/assets/vendors/css/vendor.bundle.base.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/skydash/dist/assets/vendors/font-awesome/css/font-awesome.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/skydash/dist/assets/css/style.css') }}">
  <link rel="shortcut icon" href="{{ asset('assets/skydash/dist/assets/images/favicon.png') }}" />
</head>
<body>
  <div class="container-scroller">
    <div class="container-fluid page-body-wrapper full-page-wrapper">
      <div class="content-wrapper d-flex align-items-center auth px-0">
        <div class="row w-100 mx-0">
          <div class="col-lg-4 mx-auto">
            <div class="auth-form-light text-left py-5 px-4 px-sm-5">
              <div class="brand-logo">
                <img src="{{ asset('assets/skydash/dist/assets/images/logo.svg') }}" alt="logo">
              </div>
              <h4>Admin Login</h4>
              <h6 class="fw-light">Masuk untuk mengelola materi dan quiz.</h6>

              @if ($errors->any())
                <div class="alert alert-danger">
                  @foreach ($errors->all() as $error)
                    <p class="mb-0">{{ $error }}</p>
                  @endforeach
                </div>
              @endif

              <form class="pt-3" method="POST" action="{{ route('admin.login') }}">
                @csrf
                <div class="form-group">
                  <input type="text" class="form-control form-control-lg" name="username" placeholder="NIS / Username" value="{{ old('username') }}" required>
                </div>
                <div class="form-group">
                  <input type="password" class="form-control form-control-lg" name="password" placeholder="Password" required>
                </div>
                <div class="mt-3 d-grid">
                  <button type="submit" class="btn btn-primary btn-lg btn-block">SIGN IN</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="{{ asset('assets/skydash/dist/assets/vendors/js/vendor.bundle.base.js') }}"></script>
</body>
</html>
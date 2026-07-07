<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login - NetLabs</title>
  <link rel="stylesheet" href="{{ asset('assets/netlabs/dist/assets/vendors/feather/feather.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/netlabs/dist/assets/vendors/ti-icons/css/themify-icons.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/netlabs/dist/assets/vendors/css/vendor.bundle.base.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/netlabs/dist/assets/vendors/font-awesome/css/font-awesome.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/netlabs/dist/assets/css/style.css') }}">
  <link rel="shortcut icon" href="{{ asset('assets/netlabs/dist/assets/images/favicon.png') }}" />
</head>
<body>
  <div class="container-scroller">
    <div class="container-fluid page-body-wrapper full-page-wrapper">
      <div class="content-wrapper d-flex align-items-center auth px-0">
        <div class="row w-100 mx-0">
          <div class="col-lg-4 mx-auto">
            <div class="auth-form-light text-left py-5 px-4 px-sm-5">
              <div class="brand-logo d-flex align-items-center mb-3">
                <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #4B49AC, #7978E9); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px;" class="me-2">
                  <i class="ti-server"></i>
                </div>
                <span style="font-size: 28px; font-weight: 800; color: #4B49AC; letter-spacing: -0.5px;">NetLabs</span>
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
  <script src="{{ asset('assets/netlabs/dist/assets/vendors/js/vendor.bundle.base.js') }}"></script>
</body>
</html>
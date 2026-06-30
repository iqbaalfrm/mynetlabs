<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'NetLabs Admin')</title>
  <link rel="stylesheet" href="{{ asset('assets/skydash/dist/assets/vendors/feather/feather.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/skydash/dist/assets/vendors/ti-icons/css/themify-icons.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/skydash/dist/assets/vendors/css/vendor.bundle.base.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/skydash/dist/assets/vendors/font-awesome/css/font-awesome.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/skydash/dist/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/skydash/dist/assets/css/style.css') }}">
  <link rel="shortcut icon" href="{{ asset('assets/skydash/dist/assets/images/favicon.png') }}" />
  @stack('styles')
  <style>
    .sidebar-offcanvas {
      position: fixed !important;
      top: 70px !important;
      left: 0 !important;
      height: calc(100vh - 70px) !important;
      overflow-y: auto !important;
      z-index: 1000 !important;
      width: 260px !important;
    }
    .main-panel {
      margin-left: 260px !important;
      width: calc(100% - 260px) !important;
    }
    @media (max-width: 991px) {
      .sidebar-offcanvas {
        transform: translateX(-100%);
        transition: transform 0.25s ease-in-out;
      }
      .sidebar-offcanvas.active {
        transform: translateX(0);
      }
      .main-panel {
        margin-left: 0 !important;
        width: 100% !important;
      }
    }
  </style>
</head>
<body>
  <div class="container-scroller">
    <!-- Navbar -->
    <nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
      <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
        <a class="navbar-brand brand-logo" href="{{ url('/') }}">
          <img src="{{ asset('assets/skydash/dist/assets/images/logo.svg') }}" alt="logo" />
        </a>
        <a class="navbar-brand brand-logo-mini" href="{{ url('/') }}">
          <img src="{{ asset('assets/skydash/dist/assets/images/logo-mini.svg') }}" alt="logo" />
        </a>
      </div>
      <div class="navbar-menu-wrapper d-flex align-items-stretch">
        <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
          <span class="icon-menu"></span>
        </button>
        <ul class="navbar-nav navbar-nav-right">
          <li class="nav-item nav-profile dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" id="profileDropdown">
              <img src="{{ asset('assets/skydash/dist/assets/images/faces/face28.jpg') }}" alt="profile" />
            </a>
            <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
              <a class="dropdown-item" href="#">
                <i class="ti-settings text-primary"></i> Settings
              </a>
              <a class="dropdown-item" href="{{ route('admin.pengaturan.index') }}">
                <i class="ti-settings text-primary"></i> Pengaturan
              </a>
              <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="dropdown-item">
                  <i class="ti-power-off text-primary"></i> Logout
                </button>
              </form>
            </div>
          </li>
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
          <span class="icon-menu"></span>
        </button>
      </div>
    </nav>

    <div class="container-fluid page-body-wrapper">
      <!-- Sidebar -->
      <nav class="sidebar sidebar-offcanvas" id="sidebar">
        <ul class="nav">
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
              <i class="icon-grid menu-icon"></i>
              <span class="menu-title">Dashboard</span>
            </a>
          </li>

          @php
            $menuItems = [
              ['route' => 'admin.materi.index', 'icon' => 'icon-paper', 'label' => 'Pertemuan'],
              ['route' => 'admin.kelas.index', 'icon' => 'icon-head', 'label' => 'Kelas'],
              ['route' => 'admin.users.index', 'icon' => 'ti-user', 'label' => 'Users'],
              ['route' => 'admin.chat.index', 'icon' => 'ti-comment', 'label' => 'Chat AI'],
              ['route' => 'admin.pengaturan.index', 'icon' => 'ti-settings', 'label' => 'Pengaturan'],
            ];
          @endphp

          @foreach($menuItems as $item)
            @if(Route::has($item['route']))
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs($item['route'].'*') ? 'active' : '' }}" href="{{ route($item['route']) }}">
                <i class="{{ $item['icon'] }} menu-icon"></i>
                <span class="menu-title">{{ $item['label'] }}</span>
              </a>
            </li>
            @endif
          @endforeach
        </ul>
      </nav>

      <!-- Main Content -->
      <div class="main-panel">
        <div class="content-wrapper">
          @yield('content')
        </div>
        <footer class="footer">
          <div class="d-sm-flex justify-content-center justify-content-sm-between">
            <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Copyright &copy; {{ date('Y') }} NetLabs. All rights reserved.</span>
            <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center">Hand-crafted & made with <i class="ti-heart text-danger ms-1"></i></span>
          </div>
        </footer>
      </div>
    </div>
  </div>

  <script src="{{ asset('assets/skydash/dist/assets/vendors/js/vendor.bundle.base.js') }}"></script>
  <script src="{{ asset('assets/skydash/dist/assets/js/off-canvas.js') }}"></script>
  <script src="{{ asset('assets/skydash/dist/assets/js/template.js') }}"></script>
  <script src="{{ asset('assets/skydash/dist/assets/js/settings.js') }}"></script>
  <script src="{{ asset('assets/skydash/dist/assets/js/todolist.js') }}"></script>
  @stack('scripts')
</body>
</html>
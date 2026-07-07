<![CDATA[<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'NetLabs Admin')</title>
  <link rel="stylesheet" href="{{ asset('assets/netlabs/dist/assets/vendors/feather/feather.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/netlabs/dist/assets/vendors/ti-icons/css/themify-icons.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/netlabs/dist/assets/vendors/css/vendor.bundle.base.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/netlabs/dist/assets/vendors/font-awesome/css/font-awesome.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/netlabs/dist/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/netlabs/dist/assets/css/style.css') }}">
  <link rel="shortcut icon" href="{{ asset('assets/netlabs/dist/assets/images/favicon.png') }}" />
  @stack('styles')
  <style>
    :root {
      --nl-primary: #4F6AF5;
      --nl-secondary: #FF8C42;
      --nl-bg: #F8F9FF;
      --nl-sidebar-bg: #FFFFFF;
      --nl-text: #1A1D3B;
      --nl-border: #E8EAF6;
      --nl-muted: #8B8FA3;
      --nl-success: #10B981;
      --nl-danger: #EF4444;
      --nl-warning: #F59E0B;
      --nl-info: #3B82F6;
    }

    body { background: var(--nl-bg) !important; color: var(--nl-text) !important; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif !important; }

    /* ===== Sidebar ===== */
    .sidebar-offcanvas {
      position: fixed !important;
      top: 64px !important;
      left: 0 !important;
      height: calc(100vh - 64px) !important;
      overflow-y: auto !important;
      z-index: 1000 !important;
      width: 260px !important;
      background: var(--nl-sidebar-bg) !important;
      border-right: 1px solid var(--nl-border) !important;
      box-shadow: none !important;
    }
    .sidebar .nav { padding: 12px; }
    .sidebar .nav-item { margin-bottom: 2px; }
    .sidebar .nav-link {
      border-radius: 8px !important;
      padding: 10px 14px !important;
      color: var(--nl-text) !important;
      font-size: 14px !important;
      font-weight: 500 !important;
      display: flex !important;
      align-items: center !important;
      gap: 12px;
      transition: all 0.15s ease !important;
    }
    .sidebar .nav-link:hover { background: #EEF0FE !important; color: var(--nl-primary) !important; }
    .sidebar .nav-link.active { background: var(--nl-primary) !important; color: #fff !important; }
    .sidebar .nav-link.active .menu-icon { color: #fff !important; }
    .sidebar .menu-icon { font-size: 18px !important; width: 20px; text-align: center; color: var(--nl-muted); }
    .sidebar .menu-title { flex: 1; }

    /* Sidebar category headers */
    .sidebar .nav-category {
      font-size: 11px !important;
      font-weight: 600 !important;
      text-transform: uppercase !important;
      letter-spacing: 0.5px;
      color: var(--nl-muted) !important;
      padding: 16px 14px 6px !important;
      margin: 0 !important;
    }
    .sidebar .nav-category:first-child { padding-top: 4px !important; }

    /* Collapse sections */
    .sidebar .nav-section.collapsible .nav-section-toggle { cursor: pointer; }
    .sidebar .nav-section.collapsible .nav-section-body { overflow: hidden; transition: max-height 0.3s ease; }
    .sidebar .nav-section.collapsible.collapsed .nav-section-body { max-height: 0 !important; }
    .sidebar .nav-section.collapsible .chevron { transition: transform 0.2s ease; font-size: 12px; }
    .sidebar .nav-section.collapsible.collapsed .chevron { transform: rotate(-90deg); }

    /* ===== Topbar / Navbar ===== */
    .navbar.default-layout-navbar {
      background: var(--nl-sidebar-bg) !important;
      border-bottom: 1px solid var(--nl-border) !important;
      box-shadow: none !important;
      height: 64px !important;
    }
    .navbar-brand-wrapper { background: transparent !important; border: none !important; }
    .navbar-menu-wrapper { padding: 0 20px !important; }

    /* Search bar in topbar */
    .nl-search {
      position: relative;
      flex: 1;
      max-width: 400px;
    }
    .nl-search input {
      border: 1px solid var(--nl-border) !important;
      border-radius: 8px !important;
      padding: 8px 12px 8px 38px !important;
      font-size: 14px !important;
      background: var(--nl-bg) !important;
      height: 40px !important;
    }
    .nl-search input:focus { border-color: var(--nl-primary) !important; box-shadow: 0 0 0 3px rgba(79,106,245,0.1) !important; }
    .nl-search .nl-search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--nl-muted); font-size: 16px; }

    /* Profile dropdown */
    .nav-profile img { width: 36px !important; height: 36px !important; border-radius: 8px !important; object-fit: cover; }
    .dropdown-menu { border: 1px solid var(--nl-border) !important; border-radius: 10px !important; box-shadow: 0 4px 12px rgba(0,0,0,0.08) !important; padding: 6px !important; }
    .dropdown-item { border-radius: 6px !important; padding: 8px 12px !important; font-size: 14px !important; }

    /* ===== Main Panel ===== */
    .main-panel { margin-left: 260px !important; width: calc(100% - 260px) !important; }
    .content-wrapper { padding: 24px !important; background: var(--nl-bg) !important; }

    /* ===== Cards ===== */
    .card {
      border: 1px solid var(--nl-border) !important;
      border-radius: 12px !important;
      box-shadow: none !important;
      background: #fff !important;
    }
    .card-body { padding: 20px !important; }
    .card-title { font-weight: 600 !important; font-size: 16px !important; color: var(--nl-text) !important; }

    /* ===== Stat Cards ===== */
    .nl-stat-card {
      border: 1px solid var(--nl-border) !important;
      border-radius: 12px !important;
      background: #fff !important;
      padding: 20px;
      display: flex;
      align-items: center;
      gap: 16px;
    }
    .nl-stat-icon {
      width: 48px; height: 48px;
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 22px; color: #fff;
    }
    .nl-stat-icon.primary { background: var(--nl-primary); }
    .nl-stat-icon.secondary { background: var(--nl-secondary); }
    .nl-stat-icon.success { background: var(--nl-success); }
    .nl-stat-icon.warning { background: var(--nl-warning); }
    .nl-stat-label { font-size: 13px; color: var(--nl-muted); margin: 0; }
    .nl-stat-value { font-size: 28px; font-weight: 700; color: var(--nl-text); margin: 0; line-height: 1.2; }

    /* ===== Tables ===== */
    .table { color: var(--nl-text) !important; margin-bottom: 0 !important; }
    .table thead th {
      background: var(--nl-bg) !important;
      border: none !important;
      border-bottom: 2px solid var(--nl-border) !important;
      font-size: 12px !important;
      font-weight: 600 !important;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: var(--nl-muted) !important;
      padding: 12px 16px !important;
      white-space: nowrap;
    }
    .table tbody td { border: none !important; border-bottom: 1px solid var(--nl-border) !important; padding: 14px 16px !important; font-size: 14px !important; vertical-align: middle; }
    .table tbody tr:hover { background: #F8F9FF !important; }
    .table tbody tr:last-child td { border-bottom: none !important; }

    /* ===== Badges ===== */
    .badge { font-weight: 500 !important; padding: 5px 10px !important; border-radius: 6px !important; font-size: 12px !important; }
    .badge.bg-primary { background: var(--nl-primary) !important; }
    .badge.bg-success { background: var(--nl-success) !important; }
    .badge.bg-warning { background: var(--nl-warning) !important; color: #fff !important; }
    .badge.bg-danger { background: var(--nl-danger) !important; }
    .badge.bg-info { background: var(--nl-info) !important; }
    .badge.bg-secondary { background: #E8EAF6 !important; color: var(--nl-text) !important; }
    .badge-outline-info { border: 1px solid var(--nl-info) !important; color: var(--nl-info) !important; background: transparent !important; }
    .badge-outline-success { border: 1px solid var(--nl-success) !important; color: var(--nl-success) !important; background: transparent !important; }

    /* ===== Buttons ===== */
    .btn-primary { background: var(--nl-primary) !important; border-color: var(--nl-primary) !important; font-weight: 500 !important; }
    .btn-primary:hover { background: #3B54D9 !important; border-color: #3B54D9 !important; }
    .btn-outline-secondary { border-color: var(--nl-border) !important; color: var(--nl-text) !important; }
    .btn-outline-secondary:hover { background: #EEF0FE !important; border-color: var(--nl-primary) !important; color: var(--nl-primary) !important; }
    .btn-outline-danger { border-color: var(--nl-border) !important; color: var(--nl-danger) !important; }
    .btn-outline-danger:hover { background: #FEE2E2 !important; border-color: var(--nl-danger) !important; color: var(--nl-danger) !important; }
    .btn { border-radius: 8px !important; font-size: 14px !important; padding: 8px 16px !important; }
    .btn-sm { padding: 6px 12px !important; font-size: 13px !important; }

    /* ===== Forms ===== */
    .form-control, .form-select {
      border: 1px solid var(--nl-border) !important;
      border-radius: 8px !important;
      padding: 9px 12px !important;
      font-size: 14px !important;
      color: var(--nl-text) !important;
    }
    .form-control:focus, .form-select:focus {
      border-color: var(--nl-primary) !important;
      box-shadow: 0 0 0 3px rgba(79,106,245,0.1) !important;
    }
    .form-label { font-weight: 500 !important; font-size: 13px !important; color: var(--nl-text) !important; margin-bottom: 6px !important; }
    .invalid-feedback { font-size: 12px !important; }

    /* ===== Modal ===== */
    .modal-content { border: none !important; border-radius: 14px !important; box-shadow: 0 8px 24px rgba(0,0,0,0.12) !important; }
    .modal-header { border-bottom: 1px solid var(--nl-border) !important; padding: 16px 20px !important; }
    .modal-body { padding: 20px !important; }
    .modal-footer { border-top: 1px solid var(--nl-border) !important; padding: 16px 20px !important; }
    .modal-title { font-weight: 600 !important; color: var(--nl-text) !important; }

    /* ===== Tabs ===== */
    .nav-tabs { border-bottom: 1px solid var(--nl-border) !important; gap: 4px; }
    .nav-tabs .nav-link {
      border: none !important;
      border-bottom: 2px solid transparent !important;
      border-radius: 0 !important;
      color: var(--nl-muted) !important;
      font-weight: 500 !important;
      font-size: 14px !important;
      padding: 10px 16px !important;
    }
    .nav-tabs .nav-link.active { border-bottom-color: var(--nl-primary) !important; color: var(--nl-primary) !important; background: transparent !important; }

    /* ===== Alerts ===== */
    .alert { border-radius: 8px !important; border: none !important; font-size: 14px !important; padding: 12px 16px !important; }
    .alert-success { background: #ECFDF5 !important; color: var(--nl-success) !important; }
    .alert-danger { background: #FEF2F2 !important; color: var(--nl-danger) !important; }

    /* ===== Footer ===== */
    .footer { background: transparent !important; border-top: 1px solid var(--nl-border) !important; padding: 16px 24px !important; font-size: 13px !important; }

    /* ===== Pagination ===== */
    .pagination { gap: 4px; }
    .page-item .page-link { border: 1px solid var(--nl-border) !important; color: var(--nl-text) !important; border-radius: 6px !important; padding: 8px 12px !important; font-size: 14px !important; }
    .page-item.active .page-link { background: var(--nl-primary) !important; border-color: var(--nl-primary) !important; color: #fff !important; }

    /* ===== Misc ===== */
    .grid-margin { margin-bottom: 20px; }
    .stretch-card { width: 100%; }
    .text-muted { color: var(--nl-muted) !important; }
    .font-weight-bold { font-weight: 700 !important; }
    .nl-page-header { margin-bottom: 24px; }
    .nl-page-header h3 { font-weight: 700 !important; color: var(--nl-text) !important; margin: 0 0 4px; font-size: 22px; }
    .nl-page-header p { color: var(--nl-muted) !important; margin: 0; font-size: 14px; }

    @media (max-width: 991px) {
      .sidebar-offcanvas { transform: translateX(-100%); transition: transform 0.25s ease-in-out; }
      .sidebar-offcanvas.active { transform: translateX(0); }
      .main-panel { margin-left: 0 !important; width: 100% !important; }
      .nl-search { display: none; }
    }
  </style>
</head>
<body>
  <div class="container-scroller">
    <!-- Navbar / Topbar -->
    <nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
      <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start" style="width: 260px;">
        <a class="navbar-brand brand-logo d-flex align-items-center gap-2" href="{{ url('/') }}">
          <span style="font-size: 22px; font-weight: 800; color: var(--nl-primary); letter-spacing: -0.5px;">NetLabs</span>
        </a>
      </div>
      <div class="navbar-menu-wrapper d-flex align-items-center gap-3" style="flex: 1;">
        <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize" style="border: none;">
          <span class="icon-menu"></span>
        </button>
        <div class="nl-search d-none d-md-block">
          <i class="ti-search nl-search-icon"></i>
          <input type="text" class="form-control" placeholder="Cari sesuatu...">
        </div>
        <ul class="navbar-nav navbar-nav-right ms-auto d-flex align-items-center gap-2" style="flex-direction: row;">
          <li class="nav-item dropdown d-flex align-items-center">
            <a class="nav-link position-relative" href="#" data-bs-toggle="dropdown" style="padding: 8px;">
              <i class="ti-bell" style="font-size: 20px; color: var(--nl-text);"></i>
              <span class="position- top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px; top: 4px; right: 4px; left: auto;">3</span>
            </a>
            <div class="dropdown-menu dropdown-menu-right navbar-dropdown" style="min-width: 280px;">
              <h6 class="dropdown-header" style="font-weight: 600; font-size: 14px;">Notifikasi</h6>
              <a class="dropdown-item d-flex align-items-start gap-2 py-2" href="#">
                <i class="ti-user text-primary mt-1"></i>
                <div><div style="font-size: 13px; font-weight: 500;">User baru terdaftar</div><small class="text-muted">2 menit lalu</small></div>
              </a>
              <a class="dropdown-item d-flex align-items-start gap-2 py-2" href="#">
                <i class="ti-comment text-primary mt-1"></i>
                <div><div style="font-size: 13px; font-weight: 500;">Chat AI baru</div><small class="text-muted">1 jam lalu</small></div>
              </a>
              <a class="dropdown-item d-flex align-items-start gap-2 py-2" href="#">
                <i class="ti-check-box text-primary mt-1"></i>
                <div><div style="font-size: 13px; font-weight: 500;">Kuis baru disubmit</div><small class="text-muted">3 jam lalu</small></div>
              </a>
            </div>
          </li>
          <li class="nav-item nav-profile dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown" id="profileDropdown" style="padding: 4px 8px;">
              <img src="{{ asset('assets/netlabs/dist/assets/images/faces/face28.jpg') }}" alt="profile" />
              <div class="d-none d-md-block text-start">
                <div style="font-size: 13px; font-weight: 600; color: var(--nl-text); line-height: 1.2;">{{ Auth::user()->nama ?? 'Admin' }}</div>
                <div style="font-size: 11px; color: var(--nl-muted); text-transform: capitalize;">{{ Auth::user()->role ?? 'admin' }}</div>
              </div>
            </a>
            <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
              <a class="dropdown-item" href="{{ route('admin.pengaturan.index') }}">
                <i class="ti-settings me-2"></i> Pengaturan
              </a>
              <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="dropdown-item text-danger">
                  <i class="ti-power-off me-2"></i> Logout
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

          {{-- Kategori: Konten --}}
          <li class="nav-category">Konten</li>
          @php
            $kontenMenu = [
              ['route' => 'admin.materi.index', 'icon' => 'icon-paper', 'label' => 'Pertemuan'],
            ];
          @endphp
          @foreach($kontenMenu as $item)
            @if(Route::has($item['route']))
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs($item['route'].'*') ? 'active' : '' }}" href="{{ route($item['route']) }}">
                <i class="{{ $item['icon'] }} menu-icon"></i>
                <span class="menu-title">{{ $item['label'] }}</span>
              </a>
            </li>
            @endif
          @endforeach

          {{-- Kategori: Siswa --}}
          <li class="nav-category">Siswa</li>
          @php
            $siswaMenu = [
              ['route' => 'admin.users.index', 'icon' => 'ti-user', 'label' => 'Users'],
              ['route' => 'admin.kelas.index', 'icon' => 'ti-headphone-alt', 'label' => 'Kelas'],
            ];
          @endphp
          @foreach($siswaMenu as $item)
            @if(Route::has($item['route']))
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs($item['route'].'*') ? 'active' : '' }}" href="{{ route($item['route']) }}">
                <i class="{{ $item['icon'] }} menu-icon"></i>
                <span class="menu-title">{{ $item['label'] }}</span>
              </a>
            </li>
            @endif
          @endforeach

          {{-- Kategori: AI & Knowledge Base --}}
          <li class="nav-category">AI & Knowledge Base</li>
          @php
            $aiMenu = [
              ['route' => 'admin.chat.index', 'icon' => 'ti-comment', 'label' => 'Chat AI'],
            ];
          @endphp
          @foreach($aiMenu as $item)
            @if(Route::has($item['route']))
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs($item['route'].'*') ? 'active' : '' }}" href="{{ route($item['route']) }}">
                <i class="{{ $item['icon'] }} menu-icon"></i>
                <span class="menu-title">{{ $item['label'] }}</span>
              </a>
            </li>
            @endif
          @endforeach

          {{-- Kategori: Pengaturan --}}
          <li class="nav-category">Sistem</li>
          @php
            $sistemMenu = [
              ['route' => 'admin.pengaturan.index', 'icon' => 'ti-settings', 'label' => 'Pengaturan'],
            ];
          @endphp
          @foreach($sistemMenu as $item)
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
          </div>
        </footer>
      </div>
    </div>
  </div>

   <script src="{{ asset('assets/netlabs/dist/assets/vendors/js/vendor.bundle.base.js') }}"></script>
   <script src="{{ asset('assets/netlabs/dist/assets/js/off-canvas.js') }}"></script>
   <script src="{{ asset('assets/netlabs/dist/assets/js/template.js') }}"></script>
   <script src="{{ asset('assets/netlabs/dist/assets/js/settings.js') }}"></script>
   <script src="{{ asset('assets/netlabs/dist/assets/js/todolist.js') }}"></script>
   <!-- SweetAlert2 -->
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   @stack('scripts')
</body>
</html>
]]>
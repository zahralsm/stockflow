<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Dashboard') — StockFlow</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        rel="stylesheet"
    >

    <style>
        :root {
            --green: #047857;
            --ink: #19332a;
            --muted: #74847c;
            --line: #e5ece7;
        }

        body {
            background: #f5f8f6;
            color: var(--ink);
            font-family: "Segoe UI", Arial, sans-serif;
        }

        a {
            text-decoration: none;
        }

        .sidebar {
            --bs-offcanvas-width: 260px;
            background: white !important;
        }

        .sidebar .offcanvas-body {
            min-height: 0;
            overflow-y: auto !important;
            overflow-x: hidden;
            overscroll-behavior-y: contain;
        }

        .sidebar .offcanvas-body > * {
            flex-shrink: 0;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--ink);
            font-size: 23px;
            font-weight: 750;
            letter-spacing: -0.8px;
        }

        .brand-icon {
            display: grid;
            place-items: center;
            width: 42px;
            height: 42px;
            background: var(--green);
            color: white;
            border-radius: 12px;
        }

        .nav-label {
            color: #8a978f;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.5px;
            margin: 32px 12px 12px;
        }

        .side-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px 15px;
            margin-bottom: 6px;
            border-radius: 10px;
            color: #63776c;
            font-size: 14px;
            font-weight: 550;
        }

        .side-link:hover,
        .side-link.active {
            background: #e7f5ee;
            color: var(--green);
        }

        .side-link i {
            width: 18px;
            text-align: center;
        }

        .topbar {
            background: white;
            border-bottom: 1px solid var(--line);
            padding: 20px 28px;
        }

        .page-content {
            padding: 32px;
            max-width: 1440px;
            margin: auto;
        }

        .page-title {
            font-size: 28px;
            font-weight: 750;
            letter-spacing: -0.8px;
        }

        .muted {
            color: var(--muted);
        }

        .panel {
            background: white;
            border: 1px solid var(--line);
            border-radius: 17px;
            box-shadow: 0 8px 28px rgba(30, 64, 45, 0.035);
        }

        .badge-soft {
            display: inline-block;
            background: #e7f5ee;
            color: var(--green);
            padding: 6px 11px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .btn-emerald {
            background: var(--green);
            border: 1px solid var(--green);
            color: white;
            border-radius: 9px;
            padding: 10px 16px;
            font-size: 14px;
            font-weight: 600;
        }

        .btn-emerald:hover {
            background: #065f46;
            border-color: #065f46;
            color: white;
        }

        .btn-light {
            background: #f3f7f4;
            border-color: var(--line);
            color: #42604e;
            border-radius: 9px;
        }

        .form-control {
            padding: 11px 13px;
            border-color: #dfe7e1;
            border-radius: 9px;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: #34a881;
            box-shadow: 0 0 0 3px rgba(4, 120, 87, 0.09);
        }

        .form-label {
            font-size: 13px;
            font-weight: 650;
        }

        .table {
            --bs-table-bg: transparent;
            font-size: 14px;
            margin-bottom: 0;
        }

        .table thead th {
            background: #f7faf8;
            color: #7a8a80;
            font-size: 11px;
            font-weight: 650;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 15px 20px;
        }

        .table tbody td {
            padding: 16px 20px;
            border-color: #edf1ee;
            vertical-align: middle;
        }

        .action-button {
            display: inline-grid;
            place-items: center;
            width: 33px;
            height: 33px;
            border: 0;
            border-radius: 8px;
            background: #edf6f1;
            color: #047857;
        }

        .action-button.danger {
            background: #fff1f0;
            color: #b42318;
        }

        .page-link {
            color: var(--green);
        }

        .active > .page-link {
            background: var(--green);
            border-color: var(--green);
        }

        .empty-state {
            padding: 48px 20px;
            text-align: center;
            color: var(--muted);
        }

        .empty-state i {
            font-size: 30px;
            color: #a4bdb0;
            margin-bottom: 15px;
        }

        .alert {
            border-radius: 12px;
            font-size: 14px;
        }

        @media (min-width: 992px) {
            .sidebar {
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
                width: 250px;
                height: 100vh;
                height: 100dvh;
                overflow: hidden;
                border-right: 1px solid var(--line);
            }

            .sidebar .offcanvas-body {
                height: 100%;
                max-height: 100%;
            }

            .app-main {
                margin-left: 250px;
            }
        }

        @media (max-width: 991px) {
            .page-content {
                padding: 24px 16px;
            }

            .topbar {
                padding: 16px;
            }
        }
    </style>
</head>

<body>
    @php
        $isCustomer = strtolower(auth()->user()->role ?? '') === 'customer';
    @endphp
    <aside
        class="offcanvas-lg offcanvas-start sidebar"
        tabindex="-1"
        id="sidebar"
        aria-labelledby="sidebar-title"
    >
        <div class="offcanvas-header">
            <h2 class="h5 mb-0" id="sidebar-title">Navigasi</h2>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="offcanvas"
                data-bs-target="#sidebar"
                aria-label="Tutup menu"
            ></button>
        </div>

        <div class="offcanvas-body d-flex flex-column p-4">
            <a href="{{ $isCustomer ? route('products.index') : route('dashboard') }}" class="brand">
                <span class="brand-icon">
                    <i class="fa-solid fa-cube" aria-hidden="true"></i>
                </span>

                <span>StockFlow<span style="color: #059669;">.</span></span>
            </a>

            <div class="nav-label">RUANG KERJA</div>

            <nav aria-label="Menu utama">
                @unless ($isCustomer)
                    <a
                        href="{{ route('dashboard') }}"
                        class="side-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                    >
                        <i class="fa-solid fa-house" aria-hidden="true"></i>
                        Dashboard
                    </a>
                @endunless

                <a
                    href="{{ route('products.index') }}"
                    class="side-link {{ request()->routeIs('products.*') ? 'active' : '' }}"
                >
                    <i class="fa-solid fa-box-open" aria-hidden="true"></i>
                    Produk
                </a>

                @unless ($isCustomer)
                    <a
                        href="{{ route('purchases.index') }}"
                        class="side-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}"
                    >
                        <i class="fa-solid fa-cart-flatbed" aria-hidden="true"></i>
                        Pembelian
                    </a>

                    <a
                        href="{{ route('sales.index') }}"
                        class="side-link {{ request()->routeIs('sales.*') ? 'active' : '' }}"
                    >
                        <i class="fa-solid fa-cash-register" aria-hidden="true"></i>
                        Penjualan
                    </a>
                @endunless

                @if (!$isCustomer && auth()->user()->isAdmin())
                    <a
                        href="{{ route('categories.index') }}"
                        class="side-link {{ request()->routeIs('categories.*') ? 'active' : '' }}"
                    >
                        <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
                        Kategori
                    </a>

                    <a
                        href="{{ route('suppliers.index') }}"
                        class="side-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}"
                    >
                        <i class="fa-solid fa-truck-field" aria-hidden="true"></i>
                        Pemasok
                    </a>

                    <a
                        href="{{ route('reports.index') }}"
                        class="side-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"
                    >
                        <i class="fa-solid fa-chart-column" aria-hidden="true"></i>
                        Laporan
                    </a>

                    <a
                        href="{{ route('cancellations.index') }}"
                        class="side-link {{ request()->routeIs('cancellations.*') ? 'active' : '' }}"
                    >
                        <i class="fa-solid fa-ban" aria-hidden="true"></i>
                        Pembatalan
                    </a>

                    <a
                        href="{{ route('users.index') }}"
                        class="side-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                    >
                        <i class="fa-solid fa-users" aria-hidden="true"></i>
                        Kelola User
                    </a>
                @endif
            </nav>

            <div class="mt-auto pt-5">
                <div class="border-top pt-3">
                    <div class="fw-semibold small">
                        {{ auth()->user()->name }}
                    </div>

                    <div class="muted small mt-1 mb-3">
                        {{ $isCustomer ? 'Customer' : (auth()->user()->isAdmin() ? 'Admin / Owner' : 'Staff') }}
                    </div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf

                        <button class="btn btn-light w-100" type="submit">
                            <i class="fa-solid fa-arrow-right-from-bracket me-2" aria-hidden="true"></i>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <div class="app-main">
        <header class="topbar d-flex align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <button
                    class="btn btn-light d-lg-none"
                    type="button"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#sidebar"
                    aria-controls="sidebar"
                    aria-label="Buka menu"
                >
                    <i class="fa-solid fa-bars" aria-hidden="true"></i>
                </button>

                <span class="fw-semibold">@yield('title', 'Dashboard')</span>
            </div>

            <span class="badge-soft">
                {{ $isCustomer ? 'Customer' : (auth()->user()->isAdmin() ? 'Admin / Owner' : 'Staff') }}
            </span>
        </header>

        <main class="page-content">
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <div class="fw-semibold mb-1">Periksa kembali data Anda.</div>

                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @if (session('success'))
        <div class="toast-container position-fixed top-0 end-0 p-3">
            <div
                class="toast border-0"
                id="flash-toast"
                role="status"
                aria-live="polite"
                aria-atomic="true"
            >
                <div class="toast-header">
                    <i class="fa-solid fa-circle-check text-success me-2" aria-hidden="true"></i>
                    <strong class="me-auto">StockFlow</strong>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="toast"
                        aria-label="Tutup notifikasi"
                    ></button>
                </div>

                <div class="toast-body">
                    {{ session('success') }}
                </div>
            </div>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const flashToast = document.getElementById('flash-toast');

        if (flashToast) {
            new bootstrap.Toast(flashToast, {
                delay: 5000
            }).show();
        }
    </script>
</body>
</html>
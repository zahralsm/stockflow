@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="muted small mb-2">RINGKASAN BISNIS</div>

            <h1 class="page-title mb-2">
                Halo, {{ auth()->user()->name }}!
            </h1>

            <p class="muted mb-0">
                Pantau penjualan, transaksi, dan persediaan barang Anda.
            </p>
        </div>

        <span class="badge-soft">
            <i class="fa-regular fa-calendar me-2" aria-hidden="true"></i>
            {{ now()->format('d/m/Y') }}
        </span>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <section class="panel p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="muted small">Total produk</span>
                    <i class="fa-solid fa-box-open text-success" aria-hidden="true"></i>
                </div>

                <div class="h2 fw-bold mb-2">
                    {{ number_format($totalProducts, 0, ',', '.') }}
                </div>

                <div class="muted small">Jenis produk terdaftar</div>
            </section>
        </div>

        <div class="col-sm-6 col-xl-3">
            <section class="panel p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="muted small">Penjualan hari ini</span>
                    <i class="fa-solid fa-cash-register text-success" aria-hidden="true"></i>
                </div>

                <div class="h4 fw-bold mb-2">
                    Rp {{ number_format($todaySales, 0, ',', '.') }}
                </div>

                <div class="muted small">Transaksi dikonfirmasi</div>
            </section>
        </div>

        <div class="col-sm-6 col-xl-3">
            <section class="panel p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="muted small">Penjualan bulan ini</span>
                    <i class="fa-solid fa-chart-line text-success" aria-hidden="true"></i>
                </div>

                <div class="h4 fw-bold mb-2">
                    Rp {{ number_format($monthSales, 0, ',', '.') }}
                </div>

                <div class="muted small">
                    Periode {{ now()->format('m/Y') }}
                </div>
            </section>
        </div>

        <div class="col-sm-6 col-xl-3">
            <section class="panel p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="muted small">Total transaksi</span>
                    <i class="fa-solid fa-receipt text-success" aria-hidden="true"></i>
                </div>

                <div class="h2 fw-bold mb-2">
                    {{ number_format($totalTransactions, 0, ',', '.') }}
                </div>

                <div class="muted small">
                    Pembelian + penjualan, semua status
                </div>
            </section>
        </div>
    </div>

    <section class="panel p-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="h5 fw-bold mb-1">Mulai transaksi</h2>
                <p class="muted small mb-0">
                    Catat barang masuk dan keluar untuk memperbarui persediaan.
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('purchases.index') }}" class="btn btn-light">
                    <i class="fa-solid fa-arrow-down me-2" aria-hidden="true"></i>
                    Pembelian
                </a>

                <a href="{{ route('sales.index') }}" class="btn btn-emerald">
                    <i class="fa-solid fa-plus me-2" aria-hidden="true"></i>
                    Penjualan
                </a>
            </div>
        </div>
    </section>

    <div class="row g-4">
        <div class="col-xl-6">
            <section class="panel overflow-hidden h-100">
                <div class="p-4 border-bottom">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <h2 class="h5 fw-bold mb-0">Perlu restok</h2>

                        <span class="badge rounded-pill text-bg-warning">
                            {{ $lowStockCount }} produk
                        </span>
                    </div>

                    <p class="muted small mt-2 mb-0">
                        Stok sama dengan atau di bawah batas minimum.
                        Menampilkan maksimal 8 produk.
                    </p>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">Produk</th>
                                <th scope="col" class="text-center">Stok</th>
                                <th scope="col" class="text-center">Minimum</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($lowStockProducts as $product)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">
                                            {{ $product->name }}
                                        </div>

                                        <div class="muted small mt-1">
                                            {{ $product->sku }}
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge rounded-pill text-bg-warning">
                                            {{ $product->stock }}
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        {{ $product->minimum_stock }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">
                                        <div class="empty-state">
                                            <i
                                                class="fa-solid fa-circle-check"
                                                aria-hidden="true"
                                            ></i>

                                            <div class="fw-semibold">
                                                {{ $totalProducts > 0
                                                    ? 'Persediaan di atas batas minimum'
                                                    : 'Belum ada produk' }}
                                            </div>

                                            <div class="small mt-1">
                                                {{ $totalProducts > 0
                                                    ? 'Tidak ada produk yang perlu restok saat ini.'
                                                    : 'Tambahkan produk melalui akun Admin.' }}
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-3 border-top">
                    <a
                        href="{{ route('products.index', ['stock_status' => 'low']) }}"
                        class="btn btn-light btn-sm"
                    >
                        Lihat stok rendah
                        <i class="fa-solid fa-arrow-right ms-2" aria-hidden="true"></i>
                    </a>
                </div>
            </section>
        </div>

        <div class="col-xl-6">
            <section class="panel overflow-hidden h-100">
                <div class="p-4 border-bottom">
                    <h2 class="h5 fw-bold mb-2">Penjualan terbaru</h2>
                    <p class="muted small mb-0">Lima transaksi penjualan terakhir.</p>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">Transaksi</th>
                                <th scope="col">Total</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($latestSales as $sale)
                                <tr>
                                    <td>
                                        <div class="small fw-semibold">
                                            {{ $sale->sale_number }}
                                        </div>

                                        <div class="muted small mt-1">
                                            {{ $sale->sold_at->format('d/m/Y H:i') }}
                                        </div>

                                        <div class="muted small">
                                            {{ $sale->user?->name ?? '—' }}
                                        </div>
                                    </td>

                                    <td class="fw-semibold text-nowrap">
                                        Rp {{ number_format($sale->total_amount, 0, ',', '.') }}
                                    </td>

                                    <td>
                                        @if ($sale->status === 'confirmed')
                                            <span class="badge-soft">
                                                Dikonfirmasi
                                            </span>
                                        @else
                                            <span class="badge rounded-pill text-bg-secondary">
                                                Dibatalkan
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">
                                        <div class="empty-state">
                                            <i
                                                class="fa-solid fa-receipt"
                                                aria-hidden="true"
                                            ></i>

                                            <div class="fw-semibold">
                                                Belum ada penjualan
                                            </div>

                                            <div class="small mt-1">
                                                Penjualan yang disimpan akan muncul di sini.
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-3 border-top">
                    <a href="{{ route('sales.index') }}" class="btn btn-light btn-sm">
                        Lihat semua penjualan
                        <i class="fa-solid fa-arrow-right ms-2" aria-hidden="true"></i>
                    </a>
                </div>
            </section>
        </div>
    </div>
@endsection
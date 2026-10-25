```php
@extends('layouts.app')

@section('title', 'Katalog Produk')

@section('content')
<style>
    .catalog-header {
        background: linear-gradient(135deg, #047857, #059669);
        color: white;
        border-radius: 17px;
        padding: 28px;
        margin-bottom: 25px;
        box-shadow: 0 8px 24px rgba(4, 120, 87, 0.10);
    }

    .catalog-search {
        background: white;
        border: 1px solid #e5ece7;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 28px;
    }

    .product-card {
        height: 100%;
        overflow: hidden;
        border: 1px solid #e5ece7;
        border-radius: 15px;
        background: white;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .product-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(25, 51, 42, .08);
    }

    .product-image {
        height: 155px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f0f7f3;
        color: #047857;
    }

    .product-image i {
        font-size: 3rem;
    }

    .product-category {
        display: inline-block;
        padding: 5px 10px;
        background: #e7f5ee;
        color: #047857;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 650;
    }

    .product-name {
        color: #19332a;
        font-size: 15px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .product-price {
        color: #047857;
        font-size: 17px;
        font-weight: 750;
    }

    .stock-badge {
        display: inline-block;
        padding: 5px 9px;
        background: #f3f7f4;
        border: 1px solid #e5ece7;
        color: #42604e;
        border-radius: 8px;
        font-size: 12px;
    }

    .catalog-label {
        display: block;
        margin-bottom: 7px;
        color: #52665b;
        font-size: 13px;
        font-weight: 650;
    }

    .catalog-search .form-control,
    .catalog-search .form-select {
        min-height: 43px;
    }

    .empty-catalog {
        background: white;
        border: 1px solid #e5ece7;
        border-radius: 15px;
        padding: 55px 20px;
        text-align: center;
        color: #74847c;
    }

    @media (max-width: 575px) {
        .catalog-header {
            padding: 22px 18px;
        }
    }
</style>

{{-- Header katalog --}}
<div class="catalog-header">
    <div class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center justify-content-center rounded-3"
             style="width:52px;height:52px;background:rgba(255,255,255,.16);flex-shrink:0;">
            <i class="fa-solid fa-box-open fs-4"></i>
        </div>

        <div>
            <h2 class="h4 fw-bold mb-1">Katalog Produk</h2>
            <p class="mb-0 small" style="color:#d8f3e6;">
                Lihat produk, harga, dan ketersediaan stok terkini.
            </p>
        </div>
    </div>
</div>

{{-- Pencarian dan filter --}}
<div class="catalog-search">
    <form action="{{ route('customer.catalog') }}" method="GET">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-5">
                <label for="search" class="catalog-label">Cari Produk</label>
                <div class="input-group">
                    <span class="input-group-text bg-white">
                        <i class="fa-solid fa-magnifying-glass muted"></i>
                    </span>
                    <input
                        type="text"
                        id="search"
                        name="search"
                        class="form-control"
                        placeholder="Masukkan nama produk..."
                        value="{{ request('search') }}"
                    >
                </div>
            </div>

            <div class="col-12 col-md-4">
                <label for="category" class="catalog-label">Kategori</label>
                <select name="category" id="category" class="form-select">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $category)
                        <option
                            value="{{ $category->id }}"
                            {{ request('category') == $category->id ? 'selected' : '' }}
                        >
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-3">
                <button type="submit" class="btn btn-emerald w-100">
                    <i class="fa-solid fa-filter me-1"></i>
                    Filter
                </button>
            </div>
        </div>
    </form>
</div>

{{-- Judul daftar produk --}}
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h3 class="h5 fw-bold mb-1">Produk Tersedia</h3>
        <p class="muted small mb-0">Pilih produk untuk melihat informasi harga dan stok.</p>
    </div>

    @if (request('search') || request('category'))
        <a href="{{ route('customer.catalog') }}" class="btn btn-light btn-sm">
            <i class="fa-solid fa-rotate-left me-1"></i>
            Reset Filter
        </a>
    @endif
</div>

{{-- Kartu produk --}}
<div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 g-4">
    @forelse ($products as $product)
        <div class="col">
            <div class="product-card">
                <div class="product-image">
                    <i class="fa-solid fa-box"></i>
                </div>

                <div class="p-3 d-flex flex-column h-auto">
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                        <span class="product-category">
                            {{ $product->category->name ?? 'Umum' }}
                        </span>

                        @if ($product->stock > 0)
                            <span class="small text-success fw-semibold">
                                <i class="fa-solid fa-circle-check me-1"></i>
                                Tersedia
                            </span>
                        @else
                            <span class="small text-danger fw-semibold">
                                <i class="fa-solid fa-circle-xmark me-1"></i>
                                Habis
                            </span>
                        @endif
                    </div>

                    <h4 class="product-name mb-1">
                        {{ $product->name }}
                    </h4>

                    <p class="muted small mb-3">
                        SKU: {{ $product->sku ?? '-' }}
                    </p>

                    <div class="border-top pt-3 mt-auto d-flex justify-content-between align-items-end gap-2">
                        <div>
                            <span class="muted small d-block mb-1">Harga jual</span>
                            <div class="product-price">
                                Rp {{ number_format($product->sale_price ?? 0, 0, ',', '.') }}
                            </div>
                        </div>

                        <span class="stock-badge">
                            <i class="fa-solid fa-boxes-stacked me-1"></i>
                            {{ $product->stock }} unit
                        </span>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="empty-catalog">
                <i class="fa-solid fa-box-open mb-3"
                   style="font-size:42px;color:#a4bdb0;"></i>

                <h4 class="h5 fw-bold">Produk tidak ditemukan</h4>

                <p class="small mb-3">
                    Coba gunakan kata kunci atau kategori yang berbeda.
                </p>

                <a href="{{ route('customer.catalog') }}" class="btn btn-emerald btn-sm">
                    Tampilkan Semua Produk
                </a>
            </div>
        </div>
    @endforelse
</div>

{{-- Pagination --}}
@if ($products->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $products->withQueryString()->links() }}
    </div>
@endif
@endsection
```
@extends('layouts.app')

@section('title', 'Produk')

@section('content')
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">Persediaan produk</h1>

            <p class="muted mb-0">
                Kelola informasi produk dan pantau ketersediaan stok.
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <span class="badge-soft">
                {{ number_format($totalProducts, 0, ',', '.') }} produk
            </span>

            <span class="badge rounded-pill text-bg-warning align-self-center px-3 py-2">
                {{ number_format($lowStockCount, 0, ',', '.') }} stok rendah
            </span>
        </div>
    </div>

    <div class="row g-4">
        @if (auth()->user()->isAdmin())
            <div class="col-xl-4">
                <section class="panel p-4">
                    <h2 class="h5 fw-bold mb-2">
                        {{ $editingProduct ? 'Edit produk' : 'Tambah produk' }}
                    </h2>

                    <p class="muted small mb-4">
                        Isi identitas, harga, dan batas minimum stok produk.
                    </p>

                    @if ($categories->isEmpty())
                        <div class="alert alert-warning">
                            Buat kategori terlebih dahulu sebelum menambahkan produk.

                            <a
                                href="{{ route('categories.index') }}"
                                class="d-block mt-2 fw-semibold"
                            >
                                Buka kategori
                            </a>
                        </div>
                    @endif

                    <form
                        action="{{ $editingProduct
                            ? route('products.update', $editingProduct)
                            : route('products.store') }}"
                        method="POST"
                    >
                        @csrf

                        @if ($editingProduct)
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label for="product-category" class="form-label">
                                Kategori
                            </label>

                            <select
                                id="product-category"
                                name="category_id"
                                class="form-select @error('category_id') is-invalid @enderror"
                                required
                            >
                                <option value="">Pilih kategori</option>

                                @foreach ($categories as $category)
                                    <option
                                        value="{{ $category->id }}"
                                        @selected(
                                            (string) old('category_id', $editingProduct?->category_id)
                                            === (string) $category->id
                                        )
                                    >
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="product-sku" class="form-label">
                                SKU
                            </label>

                            <input
                                type="text"
                                id="product-sku"
                                name="sku"
                                class="form-control @error('sku') is-invalid @enderror"
                                value="{{ old('sku', $editingProduct?->sku) }}"
                                placeholder="Contoh: ELK-001"
                                maxlength="50"
                                spellcheck="false"
                                required
                            >

                            @error('sku')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="product-name" class="form-label">
                                Nama produk
                            </label>

                            <input
                                type="text"
                                id="product-name"
                                name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $editingProduct?->name) }}"
                                placeholder="Contoh: Mouse Wireless"
                                maxlength="150"
                                required
                            >

                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label for="purchase-price" class="form-label">
                                    Harga beli (Rp)
                                </label>

                                <input
                                    type="number"
                                    id="purchase-price"
                                    name="purchase_price"
                                    class="form-control @error('purchase_price') is-invalid @enderror"
                                    value="{{ old('purchase_price', $editingProduct?->purchase_price) }}"
                                    min="0"
                                    max="1000000000"
                                    step="1"
                                    placeholder="65000"
                                    required
                                >

                                @error('purchase_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-sm-6">
                                <label for="selling-price" class="form-label">
                                    Harga jual (Rp)
                                </label>

                                <input
                                    type="number"
                                    id="selling-price"
                                    name="selling_price"
                                    class="form-control @error('selling_price') is-invalid @enderror"
                                    value="{{ old('selling_price', $editingProduct?->selling_price) }}"
                                    min="0"
                                    max="1000000000"
                                    step="1"
                                    placeholder="95000"
                                    required
                                >

                                @error('selling_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="minimum-stock" class="form-label">
                                Stok minimum
                            </label>

                            <input
                                type="number"
                                id="minimum-stock"
                                name="minimum_stock"
                                class="form-control @error('minimum_stock') is-invalid @enderror"
                                value="{{ old('minimum_stock', $editingProduct?->minimum_stock ?? 5) }}"
                                min="0"
                                max="1000000"
                                step="1"
                                required
                            >

                            @error('minimum_stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="rounded-3 p-3 mb-4" style="background: #f1f7f3;">
                            <div class="small fw-semibold mb-1">
                                <i class="fa-solid fa-boxes-stacked me-1" aria-hidden="true"></i>
                                Stok {{ $editingProduct ? 'saat ini' : 'awal' }}:
                                {{ number_format($editingProduct?->stock ?? 0, 0, ',', '.') }}
                            </div>

                            <div class="small muted">
                                Stok bertambah melalui pembelian dan berkurang melalui penjualan.
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="btn btn-emerald w-100"
                            @disabled($categories->isEmpty())
                        >
                            <i class="fa-solid fa-check me-2" aria-hidden="true"></i>
                            {{ $editingProduct ? 'Simpan perubahan' : 'Simpan produk' }}
                        </button>

                        @if ($editingProduct)
                            <a
                                href="{{ route('products.index') }}"
                                class="btn btn-light w-100 mt-2"
                            >
                                Batal edit
                            </a>
                        @endif
                    </form>
                </section>
            </div>
        @endif

        <div class="{{ auth()->user()->isAdmin() ? 'col-xl-8' : 'col-12' }}">
            <section class="panel overflow-hidden">
                <div class="p-4 border-bottom">
                    <h2 class="h5 fw-bold mb-3">Daftar produk</h2>

                    <form action="{{ route('products.index') }}" method="GET">
                        <div class="row g-2">
                            <div class="col-12">
                                <input
                                    type="search"
                                    name="q"
                                    value="{{ $search }}"
                                    class="form-control"
                                    placeholder="Cari SKU atau nama produk..."
                                    aria-label="Cari SKU atau nama produk"
                                    maxlength="150"
                                >
                            </div>

                            <div class="col-md-6">
                                <select
                                    name="category_id"
                                    class="form-select"
                                    aria-label="Filter kategori"
                                >
                                    <option value="">Semua kategori</option>

                                    @foreach ($categories as $category)
                                        <option
                                            value="{{ $category->id }}"
                                            @selected((string) $categoryId === (string) $category->id)
                                        >
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <select
                                    name="stock_status"
                                    class="form-select"
                                    aria-label="Filter status stok"
                                >
                                    <option value="">Semua status stok</option>

                                    <option value="low" @selected($stockStatus === 'low')>
                                        Stok rendah
                                    </option>

                                    <option value="safe" @selected($stockStatus === 'safe')>
                                        Stok aman
                                    </option>
                                </select>
                            </div>

                            <div class="col-12 d-flex gap-2 mt-3">
                                <button type="submit" class="btn btn-emerald">
                                    <i class="fa-solid fa-magnifying-glass me-2" aria-hidden="true"></i>
                                    Cari produk
                                </button>

                                <a
                                    href="{{ route('products.index') }}"
                                    class="btn btn-light"
                                >
                                    Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle" style="min-width: 760px;">
                        <thead>
                            <tr>
                                <th scope="col">Produk</th>
                                <th scope="col">Kategori</th>
                                <th scope="col">Harga beli</th>
                                <th scope="col">Harga jual</th>
                                <th scope="col">Stok</th>

                                @if (auth()->user()->isAdmin())
                                    <th scope="col" class="text-end">Aksi</th>
                                @endif
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($products as $product)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">
                                            {{ $product->name }}
                                        </div>

                                        <div class="small muted mt-1">
                                            {{ $product->sku }}
                                        </div>
                                    </td>

                                    <td>
                                        {{ $product->category->name }}
                                    </td>

                                    <td class="text-nowrap">
                                        Rp {{ number_format($product->purchase_price, 0, ',', '.') }}
                                    </td>

                                    <td class="text-nowrap">
                                        Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                                    </td>

                                    <td class="text-nowrap">
                                        <div class="fw-semibold">
                                            {{ number_format($product->stock, 0, ',', '.') }}
                                        </div>

                                        @if ($product->stock <= $product->minimum_stock)
                                            <span class="badge text-bg-warning mt-1">
                                                Rendah
                                            </span>
                                        @else
                                            <span class="badge-soft mt-1">
                                                Aman
                                            </span>
                                        @endif

                                        <div class="small muted mt-1">
                                            Min. {{ number_format($product->minimum_stock, 0, ',', '.') }}
                                        </div>
                                    </td>

                                    @if (auth()->user()->isAdmin())
                                        <td>
                                            <div class="d-flex justify-content-end gap-2">
                                                <a
                                                    href="{{ route('products.index', ['edit' => $product->id]) }}"
                                                    class="action-button"
                                                    aria-label="Edit produk {{ $product->name }}"
                                                    title="Edit produk"
                                                >
                                                    <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                                </a>

                                                <form
                                                    action="{{ route('products.destroy', $product) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Yakin ingin menghapus produk ini?');"
                                                >
                                                    @csrf
                                                    @method('DELETE')

                                                    <button
                                                        type="submit"
                                                        class="action-button danger"
                                                        aria-label="Hapus produk {{ $product->name }}"
                                                        title="Hapus produk"
                                                    >
                                                        <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ auth()->user()->isAdmin() ? 6 : 5 }}">
                                        <div class="empty-state">
                                            <i class="fa-solid fa-box-open" aria-hidden="true"></i>

                                            <div class="fw-semibold mb-1">
                                                Tidak ada produk untuk ditampilkan
                                            </div>

                                            <div class="small">
                                                Coba ubah filter pencarian atau tambahkan produk baru.
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-3 border-top">
                    <div class="small muted mb-2">
                        {{ number_format($products->total(), 0, ',', '.') }} produk ditemukan
                    </div>

                    @if ($products->hasPages())
                        {{ $products->links('pagination::bootstrap-5') }}
                    @endif
                </div>
            </section>
        </div>
    </div>
@endsection
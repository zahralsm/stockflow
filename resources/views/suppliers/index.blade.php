@extends('layouts.app')

@section('title', 'Pemasok')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title mb-2">Pemasok barang</h1>
            <p class="muted mb-0">
                Kelola mitra pemasok dan informasi kontak untuk pembelian barang.
            </p>
        </div>

        <span class="badge-soft">
            {{ number_format($totalSuppliers, 0, ',', '.') }} pemasok
        </span>
    </div>

    <div class="row g-4">
        <div class="col-xl-4">
            <section class="panel p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="brand-icon">
                        <i class="fa-solid fa-truck-field" aria-hidden="true"></i>
                    </span>

                    <div>
                        <h2 class="h5 fw-bold mb-1">
                            {{ $editingSupplier ? 'Edit pemasok' : 'Tambah pemasok' }}
                        </h2>

                        <div class="muted small">
                            Informasi mitra bisnis Anda.
                        </div>
                    </div>
                </div>

                <p class="muted small mb-4">
                    Nama pemasok wajib diisi. Nomor telepon dan email boleh dikosongkan.
                </p>

                <form
                    action="{{ $editingSupplier
                        ? route('suppliers.update', $editingSupplier)
                        : route('suppliers.store') }}"
                    method="POST"
                >
                    @csrf

                    @if ($editingSupplier)
                        @method('PUT')
                    @endif

                    <div class="mb-3">
                        <label for="name" class="form-label">
                            Nama pemasok <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $editingSupplier?->name) }}"
                            placeholder="Contoh: CV Mitra Sejahtera"
                            maxlength="150"
                            autocomplete="organization"
                            required
                        >

                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">
                            Nomor telepon
                        </label>

                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            class="form-control @error('phone') is-invalid @enderror"
                            value="{{ old('phone', $editingSupplier?->phone) }}"
                            placeholder="Contoh: 081234567890"
                            maxlength="25"
                            autocomplete="tel"
                        >

                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label">
                            Alamat email
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $editingSupplier?->email) }}"
                            placeholder="Contoh: sales@mitrasejahtera.test"
                            maxlength="254"
                            autocomplete="email"
                        >

                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-emerald w-100">
                        <i class="fa-solid fa-check me-2" aria-hidden="true"></i>
                        {{ $editingSupplier ? 'Simpan perubahan' : 'Simpan pemasok' }}
                    </button>

                    @if ($editingSupplier)
                        <a
                            href="{{ route('suppliers.index') }}"
                            class="btn btn-light w-100 mt-2"
                        >
                            Batal edit
                        </a>
                    @endif
                </form>
            </section>

            <div class="d-flex gap-2 muted small mt-3 px-2">
                <i class="fa-solid fa-circle-info mt-1" aria-hidden="true"></i>
                <span>
                    Pemasok yang memiliki riwayat pembelian tetap disimpan
                    untuk menjaga kelengkapan transaksi.
                </span>
            </div>
        </div>

        <div class="col-xl-8">
            <section class="panel overflow-hidden">
                <div class="p-4 border-bottom">
                    <h2 class="h5 fw-bold mb-3">Daftar pemasok</h2>

                    <form action="{{ route('suppliers.index') }}" method="GET">
                        <label for="supplier-search" class="visually-hidden">
                            Cari nama, telepon, atau email pemasok
                        </label>

                        <div class="d-flex flex-wrap gap-2">
                            <input
                                type="search"
                                id="supplier-search"
                                name="q"
                                class="form-control flex-grow-1"
                                style="flex-basis: 220px;"
                                value="{{ $search }}"
                                placeholder="Cari nama, telepon, atau email..."
                                maxlength="150"
                            >

                            <button type="submit" class="btn btn-emerald">
                                <i class="fa-solid fa-magnifying-glass me-1" aria-hidden="true"></i>
                                Cari
                            </button>

                            @if ($search !== '')
                                <a
                                    href="{{ route('suppliers.index') }}"
                                    class="btn btn-light d-inline-flex align-items-center"
                                >
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle" style="min-width: 650px;">
                        <thead>
                            <tr>
                                <th scope="col">Pemasok</th>
                                <th scope="col">Kontak</th>
                                <th scope="col" class="text-center">Pembelian</th>
                                <th scope="col" class="text-end">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($suppliers as $supplier)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">
                                            {{ $supplier->name }}
                                        </div>

                                        <div class="muted small mt-1">
                                            ID #{{ $supplier->id }}
                                        </div>
                                    </td>

                                    <td>
                                        <div>
                                            <i
                                                class="fa-solid fa-phone muted me-2"
                                                aria-hidden="true"
                                            ></i>
                                            {{ $supplier->phone ?: 'Belum diisi' }}
                                        </div>

                                        <div class="muted small mt-2">
                                            <i
                                                class="fa-regular fa-envelope me-2"
                                                aria-hidden="true"
                                            ></i>
                                            {{ $supplier->email ?: 'Belum diisi' }}
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge-soft">
                                            {{ number_format($supplier->purchases_count, 0, ',', '.') }}
                                        </span>
                                    </td>

                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a
                                                href="{{ route('suppliers.index', ['edit' => $supplier->id]) }}"
                                                class="action-button"
                                                title="Edit pemasok"
                                                aria-label="Edit pemasok {{ $supplier->name }}"
                                            >
                                                <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                            </a>

                                            <form
                                                action="{{ route('suppliers.destroy', $supplier) }}"
                                                method="POST"
                                                onsubmit="return confirm('Yakin ingin menghapus pemasok ini?');"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="action-button danger"
                                                    title="Hapus pemasok"
                                                    aria-label="Hapus pemasok {{ $supplier->name }}"
                                                >
                                                    <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        <div class="empty-state">
                                            <i class="fa-solid fa-truck-field" aria-hidden="true"></i>

                                            <div class="fw-semibold mb-1">
                                                {{ $search !== ''
                                                    ? 'Pemasok tidak ditemukan'
                                                    : 'Belum ada pemasok' }}
                                            </div>

                                            <div class="small">
                                                {{ $search !== ''
                                                    ? 'Coba kata pencarian yang berbeda.'
                                                    : 'Tambahkan pemasok pertama melalui formulir di samping.' }}
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-3 border-top">
                    <div class="muted small mb-2">
                        Total {{ number_format($suppliers->total(), 0, ',', '.') }} pemasok
                        {{ $search !== '' ? 'sesuai pencarian' : '' }}
                    </div>

                    {{ $suppliers->links('pagination::bootstrap-5') }}
                </div>
            </section>
        </div>
    </div>
@endsection
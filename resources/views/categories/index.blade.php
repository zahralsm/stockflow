@extends('layouts.app')

@section('title', 'Kategori')

@section('content')
    <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">Kategori produk</h1>

            <p class="muted mb-0">
                Atur kelompok produk untuk persediaan yang lebih rapi.
            </p>
        </div>

        <span class="badge-soft text-nowrap">
            {{ $totalCategories }} kategori
        </span>
    </div>

    <div class="row g-4">
        <div class="col-xl-4">
            <section class="panel p-4">
                <h2 class="h5 fw-bold mb-2">
                    {{ $editingCategory ? 'Edit kategori' : 'Tambah kategori' }}
                </h2>

                <p class="muted small mb-4">
                    Gunakan nama kategori yang jelas dan mudah dikenali.
                </p>

                <form
                    action="{{ $editingCategory
                        ? route('categories.update', $editingCategory)
                        : route('categories.store') }}"
                    method="POST"
                >
                    @csrf

                    @if ($editingCategory)
                        @method('PUT')
                    @endif

                    <div class="mb-4">
                        <label for="name" class="form-label">
                            Nama kategori
                        </label>

                        <input
                            type="text"
                            name="name"
                            id="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $editingCategory?->name) }}"
                            placeholder="Contoh: Elektronik"
                            maxlength="100"
                            required
                        >

                        @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-emerald w-100">
                        <i class="fa-solid fa-check me-2" aria-hidden="true"></i>
                        {{ $editingCategory ? 'Simpan perubahan' : 'Simpan kategori' }}
                    </button>

                    @if ($editingCategory)
                        <a
                            href="{{ route('categories.index') }}"
                            class="btn btn-light w-100 mt-2"
                        >
                            Batal edit
                        </a>
                    @endif
                </form>
            </section>
        </div>

        <div class="col-xl-8">
            <section class="panel overflow-hidden">
                <div class="p-4 border-bottom">
                    <h2 class="h5 fw-bold mb-3">Daftar kategori</h2>

                    <form
                        action="{{ route('categories.index') }}"
                        method="GET"
                        class="d-flex gap-2"
                    >
                        <input
                            type="search"
                            name="q"
                            value="{{ $search }}"
                            class="form-control"
                            placeholder="Cari nama kategori..."
                            aria-label="Cari nama kategori"
                            maxlength="100"
                        >

                        <button type="submit" class="btn btn-emerald">
                            Cari
                        </button>

                        @if ($search !== '')
                            <a
                                href="{{ route('categories.index') }}"
                                class="btn btn-light d-flex align-items-center"
                            >
                                Reset
                            </a>
                        @endif
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th scope="col">No.</th>
                                <th scope="col">Nama kategori</th>
                                <th scope="col">Produk</th>
                                <th scope="col" class="text-end">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($categories as $category)
                                <tr>
                                    <td class="muted">
                                        {{ $categories->firstItem() + $loop->index }}
                                    </td>

                                    <td class="fw-semibold">
                                        {{ $category->name }}
                                    </td>

                                    <td>
                                        <span class="badge-soft">
                                            {{ $category->products_count }}
                                        </span>
                                    </td>

                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a
                                                href="{{ route('categories.index', ['edit' => $category->id]) }}"
                                                class="action-button"
                                                aria-label="Edit kategori {{ $category->name }}"
                                                title="Edit kategori"
                                            >
                                                <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                            </a>

                                            <form
                                                action="{{ route('categories.destroy', $category) }}"
                                                method="POST"
                                                onsubmit="return confirm('Yakin ingin menghapus kategori ini?');"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="action-button danger"
                                                    aria-label="Hapus kategori {{ $category->name }}"
                                                    title="Hapus kategori"
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
                                            <i class="fa-solid fa-folder-open" aria-hidden="true"></i>

                                            <div class="fw-semibold mb-1">
                                                {{ $search !== ''
                                                    ? 'Kategori tidak ditemukan'
                                                    : 'Belum ada kategori' }}
                                            </div>

                                            <div class="small">
                                                {{ $search !== ''
                                                    ? 'Coba gunakan kata pencarian lain.'
                                                    : 'Tambahkan kategori melalui form di samping.' }}
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($categories->hasPages())
                    <div class="p-3 border-top">
                        {{ $categories->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection

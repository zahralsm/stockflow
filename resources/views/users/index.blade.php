@extends('layouts.app')

@section('title', 'Kelola User')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="page-title mb-2">Kelola pengguna</h1>
            <p class="muted mb-0">
                Atur akun dan hak akses tim StockFlow.
            </p>
        </div>

        <span class="badge-soft">
            <i class="fa-solid fa-user-shield me-2" aria-hidden="true"></i>
            Khusus Admin
        </span>
    </div>

    <div class="row g-4">
        <div class="col-xl-4">
            <section class="panel p-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <span class="brand-icon">
                        <i class="fa-solid fa-user-gear" aria-hidden="true"></i>
                    </span>

                    <div>
                        <h2 class="h5 fw-bold mb-1">
                            {{ $editingUser ? 'Edit pengguna' : 'Tambah pengguna' }}
                        </h2>

                        <div class="muted small">
                            Informasi akun dan akses.
                        </div>
                    </div>
                </div>

                <form
                    action="{{ $editingUser
                        ? route('users.update', $editingUser)
                        : route('users.store') }}"
                    method="POST"
                >
                    @csrf

                    @if ($editingUser)
                        @method('PUT')
                    @endif

                    <div class="mb-3">
                        <label for="name" class="form-label">
                            Nama lengkap
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $editingUser?->name) }}"
                            maxlength="100"
                            placeholder="Contoh: Staff Percobaan"
                            autocomplete="name"
                            required
                        >

                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label">
                            Username
                        </label>

                        <input
                            type="text"
                            id="username"
                            name="username"
                            class="form-control @error('username') is-invalid @enderror"
                            value="{{ old('username', $editingUser?->username) }}"
                            maxlength="50"
                            placeholder="Contoh: staffbaru"
                            autocomplete="off"
                            required
                        >

                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <div class="muted small mt-1">
                            Digunakan untuk login, tanpa spasi.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">
                            Email
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $editingUser?->email) }}"
                            maxlength="254"
                            placeholder="Contoh: staffbaru@stockflow.test"
                            autocomplete="email"
                            required
                        >

                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">
                            Role
                        </label>

                        <select
                            id="role"
                            name="role"
                            class="form-select @error('role') is-invalid @enderror"
                            required
                        >
                            <option
                                value="staff"
                                @selected(old('role', $editingUser?->role ?? 'staff') === 'staff')
                            >
                                Staff
                            </option>

                            <option
                                value="admin"
                                @selected(old('role', $editingUser?->role ?? 'staff') === 'admin')
                            >
                                Admin / Owner
                            </option>
                        </select>

                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <div class="muted small mt-1">
                            Admin memiliki akses pengelolaan penuh.
                            Staff menangani transaksi dan melihat produk.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            {{ $editingUser ? 'Password baru' : 'Password' }}
                        </label>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            minlength="8"
                            maxlength="72"
                            autocomplete="new-password"
                            @required(!$editingUser)
                        >

                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <div class="muted small mt-1">
                            {{ $editingUser
                                ? 'Kosongkan jika tidak ingin mengganti password.'
                                : 'Gunakan minimal 8 karakter.' }}
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">
                            Konfirmasi password
                        </label>

                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            class="form-control"
                            minlength="8"
                            maxlength="72"
                            autocomplete="new-password"
                            @required(!$editingUser)
                        >
                    </div>

                    <button type="submit" class="btn btn-emerald w-100">
                        <i class="fa-solid fa-check me-2" aria-hidden="true"></i>
                        {{ $editingUser ? 'Simpan perubahan' : 'Buat akun' }}
                    </button>

                    @if ($editingUser)
                        <a
                            href="{{ route('users.index') }}"
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
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                        <h2 class="h5 fw-bold mb-0">Daftar pengguna</h2>

                        <span class="badge-soft">
                            {{ number_format($users->total(), 0, ',', '.') }} akun
                        </span>
                    </div>

                    <form action="{{ route('users.index') }}" method="GET">
                        <div class="d-flex flex-wrap gap-2">
                            <input
                                type="search"
                                name="q"
                                class="form-control"
                                style="flex: 1; min-width: 200px;"
                                value="{{ $search }}"
                                maxlength="100"
                                placeholder="Cari nama, username, atau email..."
                                aria-label="Cari pengguna"
                            >

                            <button type="submit" class="btn btn-emerald">
                                <i class="fa-solid fa-magnifying-glass me-1" aria-hidden="true"></i>
                                Cari
                            </button>

                            @if ($search !== '')
                                <a
                                    href="{{ route('users.index') }}"
                                    class="btn btn-light"
                                >
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table" style="min-width: 650px;">
                        <thead>
                            <tr>
                                <th scope="col">Pengguna</th>
                                <th scope="col">Username</th>
                                <th scope="col">Role</th>
                                <th scope="col" class="text-end">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($users as $account)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">
                                            {{ $account->name }}
                                        </div>

                                        <div class="muted small mt-1 text-break">
                                            {{ $account->email }}
                                        </div>

                                        @if ($account->id === auth()->id())
                                            <div class="small text-success mt-1">
                                                <i class="fa-solid fa-circle-user me-1" aria-hidden="true"></i>
                                                Akun Anda
                                            </div>
                                        @endif
                                    </td>

                                    <td>{{ $account->username }}</td>

                                    <td>
                                        <span class="badge-soft">
                                            {{ $account->role === 'admin' ? 'Admin' : 'Staff' }}
                                        </span>
                                    </td>

                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a
                                                href="{{ route('users.index', ['edit' => $account->id]) }}"
                                                class="action-button"
                                                aria-label="Edit akun {{ $account->name }}"
                                                title="Edit akun"
                                            >
                                                <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                            </a>

                                            @if ($account->id !== auth()->id())
                                                <form
                                                    action="{{ route('users.destroy', $account) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Hapus akun ini? Akun dengan riwayat transaksi tidak dapat dihapus.');"
                                                >
                                                    @csrf
                                                    @method('DELETE')

                                                    <button
                                                        type="submit"
                                                        class="action-button danger"
                                                        aria-label="Hapus akun {{ $account->name }}"
                                                        title="Hapus akun"
                                                    >
                                                        <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        <div class="empty-state">
                                            <i class="fa-solid fa-users" aria-hidden="true"></i>

                                            <div class="fw-semibold">
                                                Pengguna tidak ditemukan
                                            </div>

                                            <div class="small mt-1">
                                                Coba kata pencarian yang berbeda.
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-3 border-top">
                    {{ $users->links('pagination::bootstrap-5') }}
                </div>
            </section>

            <p class="muted small mt-3">
                Akun yang memiliki riwayat transaksi atau pembatalan tidak dapat
                dihapus. Akun yang sedang digunakan juga dilindungi dari penghapusan.
            </p>
        </div>
    </div>
@endsection
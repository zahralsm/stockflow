@extends('layouts.app')

@section('title', 'Pembatalan Transaksi')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="page-title mb-2">Pembatalan transaksi</h1>
            <p class="muted mb-0">
                Koreksi transaksi dengan alasan yang tercatat dan penyesuaian stok.
            </p>
        </div>

        <span class="badge-soft">Khusus Admin</span>
    </div>

    <section class="panel p-4 mb-4">
        <form action="{{ route('cancellations.index') }}" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="type" class="form-label">Jenis transaksi</label>
                    <select id="type" name="type" class="form-select">
                        <option value="sales" @selected($type === 'sales')>
                            Penjualan
                        </option>
                        <option value="purchases" @selected($type === 'purchases')>
                            Pembelian
                        </option>
                    </select>
                </div>

                <div class="col-md-5">
                    <label for="q" class="form-label">Nomor transaksi</label>
                    <input
                        type="search"
                        id="q"
                        name="q"
                        class="form-control"
                        value="{{ $search }}"
                        maxlength="100"
                        placeholder="Cari nomor transaksi..."
                    >
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn btn-emerald w-100">
                        <i class="fa-solid fa-magnifying-glass me-2" aria-hidden="true"></i>
                        Tampilkan
                    </button>
                </div>
            </div>
        </form>
    </section>

    <div class="alert alert-info">
        @if ($type === 'sales')
            Pembatalan penjualan mengembalikan seluruh barang pada transaksi ke stok.
            Pastikan barang memang tidak jadi keluar atau sudah dikembalikan.
        @else
            Pembatalan pembelian mengurangi seluruh barang pada transaksi dari stok.
            Pembatalan ditolak jika persediaan saat ini tidak mencukupi.
        @endif
    </div>

    @forelse ($transactions as $transaction)
        <section class="panel p-4 mb-3">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <div class="fw-bold">
                        {{ $type === 'sales'
                            ? $transaction->sale_number
                            : $transaction->purchase_number }}
                    </div>

                    <div class="muted small mt-1">
                        {{ ($type === 'sales'
                            ? $transaction->sold_at
                            : $transaction->purchased_at)->format('d/m/Y H:i') }}
                        · {{ $transaction->user?->name ?? '—' }}
                    </div>

                    @if ($type === 'purchases')
                        <div class="muted small mt-1">
                            Pemasok: {{ $transaction->supplier?->name ?? '—' }}
                        </div>
                    @endif
                </div>

                <div class="text-end">
                    <div class="fw-bold mb-2">
                        Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                    </div>

                    @if ($transaction->status === 'confirmed')
                        <span class="badge-soft">Dikonfirmasi</span>
                    @else
                        <span class="badge rounded-pill text-bg-secondary">
                            Dibatalkan
                        </span>
                    @endif
                </div>
            </div>

            <details class="mt-3">
                <summary class="small" style="cursor: pointer;">
                    Lihat {{ $transaction->items->count() }} jenis barang
                </summary>

                <div class="table-responsive mt-2">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Jumlah</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transaction->items as $item)
                                <tr>
                                    <td>
                                        {{ $item->product_name }}
                                        <div class="muted small">{{ $item->product_sku }}</div>
                                    </td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>
                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>

            @if ($transaction->status === 'confirmed')
                <form
                    action="{{ $type === 'sales'
                        ? route('sales.cancel', $transaction)
                        : route('purchases.cancel', $transaction) }}"
                    method="POST"
                    class="border-top pt-3 mt-3"
                    onsubmit="return confirm('Batalkan transaksi ini? Stok akan disesuaikan dan transaksi tidak dapat diaktifkan kembali melalui halaman ini.');"
                >
                    @csrf

                    <label
                        for="reason-{{ $transaction->id }}"
                        class="form-label"
                    >
                        Alasan pembatalan
                    </label>

                    <div class="d-flex flex-wrap gap-2">
                        <input
                            type="text"
                            id="reason-{{ $transaction->id }}"
                            name="cancellation_reason"
                            class="form-control"
                            style="flex: 1; min-width: 220px;"
                            placeholder="Contoh: Transaksi tercatat dua kali"
                            minlength="5"
                            maxlength="1000"
                            required
                        >

                        <button type="submit" class="btn btn-outline-danger">
                            <i class="fa-solid fa-ban me-2" aria-hidden="true"></i>
                            Batalkan transaksi
                        </button>
                    </div>
                </form>
            @else
                <div class="border-top pt-3 mt-3">
                    <div class="small">
                        <strong>Alasan:</strong>
                        {{ $transaction->cancellation_reason }}
                    </div>
                    <div class="muted small mt-1">
                        Dibatalkan pada
                        {{ $transaction->cancelled_at?->format('d/m/Y H:i') ?? '—' }}
                    </div>
                </div>
            @endif
        </section>
    @empty
        <div class="panel empty-state">
            <i class="fa-solid fa-receipt" aria-hidden="true"></i>
            <div class="fw-semibold">Tidak ada transaksi yang ditemukan</div>
            <div class="small mt-1">
                Pilih jenis transaksi atau ubah nomor pencarian.
            </div>
        </div>
    @endforelse

    <div class="mt-4">
        {{ $transactions->links('pagination::bootstrap-5') }}
    </div>
@endsection
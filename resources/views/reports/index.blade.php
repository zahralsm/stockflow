@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="page-title mb-2">Laporan transaksi</h1>
            <p class="muted mb-0">
                Tinjau pembelian dan penjualan sesuai periode yang dipilih.
            </p>
        </div>

        <span class="badge-soft">
            <i class="fa-solid fa-chart-column me-2" aria-hidden="true"></i>
            Admin / Owner
        </span>
    </div>

    <section class="panel p-4 mb-4">
        <h2 class="h5 fw-bold mb-3">Filter laporan</h2>

        <form action="{{ route('reports.index') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-6 col-xl-3">
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

                <div class="col-md-6 col-xl-3">
                    <label for="start_date" class="form-label">Tanggal awal</label>
                    <input
                        type="date"
                        id="start_date"
                        name="start_date"
                        class="form-control"
                        value="{{ $startDate }}"
                        required
                    >
                </div>

                <div class="col-md-6 col-xl-3">
                    <label for="end_date" class="form-label">Tanggal akhir</label>
                    <input
                        type="date"
                        id="end_date"
                        name="end_date"
                        class="form-control"
                        value="{{ $endDate }}"
                        required
                    >
                </div>

                <div class="col-md-6 col-xl-3">
                    <label for="status" class="form-label">Status transaksi</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">Semua status</option>
                        <option value="confirmed" @selected($status === 'confirmed')>
                            Dikonfirmasi
                        </option>
                        <option value="cancelled" @selected($status === 'cancelled')>
                            Dibatalkan
                        </option>
                    </select>
                </div>

                <div class="col-md-8">
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

                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-emerald">
                        <i class="fa-solid fa-filter me-2" aria-hidden="true"></i>
                        Terapkan
                    </button>

                    <a href="{{ route('reports.index') }}" class="btn btn-light">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </section>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="panel p-4 h-100">
                <div class="muted small mb-2">Transaksi sesuai filter</div>
                <div class="h2 fw-bold mb-0">
                    {{ number_format($totalTransactions, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="panel p-4 h-100">
                <div class="muted small mb-2">
                    {{ $type === 'sales' ? 'Penjualan dikonfirmasi' : 'Pembelian dikonfirmasi' }}
                </div>
                <div class="h4 fw-bold mb-0 text-success">
                    Rp {{ number_format($confirmedAmount, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="panel p-4 h-100">
                <div class="muted small mb-2">Transaksi dibatalkan</div>
                <div class="h2 fw-bold mb-0">
                    {{ number_format($cancelledCount, 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    <p class="muted small">
        Semua ringkasan mengikuti filter.
        Nominal transaksi dibatalkan tidak dimasukkan ke total dikonfirmasi.
    </p>

    <section class="panel overflow-hidden">
        <div class="p-4 border-bottom">
            <h2 class="h5 fw-bold mb-2">
                {{ $type === 'sales' ? 'Laporan penjualan' : 'Laporan pembelian' }}
            </h2>
            <div class="muted small">
                Periode {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}
                sampai {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
            </div>
        </div>

        <div class="table-responsive">
            <table class="table" style="min-width: 850px;">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Transaksi</th>
                        <th>Petugas</th>
                        @if ($type === 'purchases')
                            <th>Pemasok</th>
                        @endif
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($transactions as $transaction)
                        <tr>
                            <td>
                                {{ $transactions->firstItem() + $loop->index }}
                            </td>

                            <td>
                                <div class="fw-semibold small">
                                    {{ $type === 'sales'
                                        ? $transaction->sale_number
                                        : $transaction->purchase_number }}
                                </div>

                                <div class="muted small mt-1">
                                    {{ ($type === 'sales'
                                        ? $transaction->sold_at
                                        : $transaction->purchased_at)->format('d/m/Y H:i') }}
                                </div>
                            </td>

                            <td>{{ $transaction->user?->name ?? '—' }}</td>

                            @if ($type === 'purchases')
                                <td>{{ $transaction->supplier?->name ?? '—' }}</td>
                            @endif

                            <td class="fw-semibold text-nowrap">
                                Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                            </td>

                            <td>
                                @if ($transaction->status === 'confirmed')
                                    <span class="badge-soft">Dikonfirmasi</span>
                                @else
                                    <span class="badge rounded-pill text-bg-secondary">
                                        Dibatalkan
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $type === 'purchases' ? 6 : 5 }}">
                                <div class="empty-state">
                                    <i class="fa-solid fa-file-circle-question" aria-hidden="true"></i>
                                    <div class="fw-semibold">Tidak ada transaksi sesuai filter</div>
                                    <div class="small mt-1">
                                        Coba ubah periode, status, atau nomor transaksi.
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3 border-top">
            {{ $transactions->links('pagination::bootstrap-5') }}
        </div>
    </section>
@endsection
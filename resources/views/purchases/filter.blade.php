<form action="{{ route('purchases.index') }}" method="GET">
    <div class="row g-3">
        <div class="col-md-6 col-xl-4">
            <label for="purchase-search" class="form-label">
                Cari transaksi
            </label>

            <input
                type="search"
                id="purchase-search"
                name="q"
                class="form-control"
                value="{{ $search }}"
                maxlength="100"
                placeholder="Nomor transaksi atau pemasok..."
            >
        </div>

        <div class="col-md-6 col-xl-3">
            <label for="purchase-start-date" class="form-label">
                Tanggal awal
            </label>

            <input
                type="date"
                id="purchase-start-date"
                name="start_date"
                class="form-control"
                value="{{ $startDate }}"
            >
        </div>

        <div class="col-md-6 col-xl-3">
            <label for="purchase-end-date" class="form-label">
                Tanggal akhir
            </label>

            <input
                type="date"
                id="purchase-end-date"
                name="end_date"
                class="form-control"
                value="{{ $endDate }}"
            >
        </div>

        <div class="col-md-6 col-xl-2">
            <label for="purchase-status" class="form-label">
                Status
            </label>

            <select
                id="purchase-status"
                name="status"
                class="form-select"
            >
                <option value="">Semua status</option>

                <option
                    value="confirmed"
                    @selected($status === 'confirmed')
                >
                    Dikonfirmasi
                </option>

                <option
                    value="cancelled"
                    @selected($status === 'cancelled')
                >
                    Dibatalkan
                </option>
            </select>
        </div>

        <div class="col-12 d-flex flex-wrap align-items-center gap-2">
            <button type="submit" class="btn btn-emerald">
                <i class="fa-solid fa-filter me-2" aria-hidden="true"></i>
                Terapkan filter
            </button>

            <a href="{{ route('purchases.index') }}" class="btn btn-light">
                Reset
            </a>

            <span class="muted small ms-md-auto">
                {{ number_format($purchases->total(), 0, ',', '.') }}
                transaksi ditemukan
            </span>
        </div>
    </div>
</form>
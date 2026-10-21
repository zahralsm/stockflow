@extends('layouts.app')

@section('title', 'Penjualan')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="page-title mb-2">Penjualan barang</h1>
            <p class="muted mb-0">
                Catat penjualan dan pantau barang yang keluar dari persediaan.
            </p>
        </div>

        <span class="badge-soft">
            <i class="fa-solid fa-arrow-up me-1" aria-hidden="true"></i>
            Barang keluar
        </span>
    </div>

    <section class="panel p-4 mb-4">
        <div class="d-flex align-items-center gap-3 mb-4">
            <span class="brand-icon">
                <i class="fa-solid fa-cash-register" aria-hidden="true"></i>
            </span>

            <div>
                <h2 class="h5 fw-bold mb-1">Penjualan baru</h2>
                <div class="muted small">
                    Pilih produk dan jumlah barang yang dijual.
                </div>
            </div>
        </div>

        @if ($products->isEmpty())
            <div class="alert alert-warning">
                Belum ada produk. Hubungi Admin untuk menambahkan produk.
            </div>
        @endif

        <form action="{{ route('sales.store') }}" method="POST" id="sale-form">
            @csrf

            <div class="mb-4">
                <label for="notes" class="form-label">Catatan penjualan</label>

                <input
                    type="text"
                    id="notes"
                    name="notes"
                    class="form-control"
                    value="{{ old('notes') }}"
                    maxlength="1000"
                    placeholder="Catatan tambahan, boleh dikosongkan"
                >
            </div>

            <div class="table-responsive mb-3">
                <table class="table" style="min-width: 850px;">
                    <thead>
                        <tr>
                            <th style="width: 35%;">Produk</th>
                            <th>Stok</th>
                            <th>Harga jual</th>
                            <th style="width: 130px;">Jumlah</th>
                            <th>Subtotal</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>

                    <tbody id="sale-items">
                        @foreach (old('items', [['product_id' => '', 'quantity' => 1]]) as $item)
                            <tr class="sale-item">
                                <td>
                                    <select
                                        name="items[{{ $loop->index }}][product_id]"
                                        class="form-select product-select"
                                        aria-label="Produk"
                                        required
                                    >
                                        <option value="" data-price="0" data-stock="0">
                                            Pilih produk
                                        </option>

                                        @foreach ($products as $product)
                                            <option
                                                value="{{ $product->id }}"
                                                data-price="{{ $product->selling_price }}"
                                                data-stock="{{ $product->stock }}"
                                                @selected((string) ($item['product_id'] ?? '') === (string) $product->id)
                                            >
                                                {{ $product->sku }} — {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <div class="stock-warning text-danger small mt-2" hidden>
                                        Jumlah melebihi stok yang ditampilkan.
                                    </div>
                                </td>

                                <td>
                                    <span class="badge-soft available-stock">0</span>
                                </td>

                                <td class="unit-price text-nowrap">Rp 0</td>

                                <td>
                                    <input
                                        type="number"
                                        name="items[{{ $loop->index }}][quantity]"
                                        class="form-control quantity-input"
                                        value="{{ $item['quantity'] ?? 1 }}"
                                        min="1"
                                        max="1000000"
                                        step="1"
                                        aria-label="Jumlah barang"
                                        required
                                    >
                                </td>

                                <td class="item-subtotal fw-semibold text-nowrap">
                                    Rp 0
                                </td>

                                <td class="text-end">
                                    <button
                                        type="button"
                                        class="action-button danger remove-item"
                                        aria-label="Hapus baris produk"
                                    >
                                        <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <button type="button" id="add-item" class="btn btn-light mb-4">
                <i class="fa-solid fa-plus me-2" aria-hidden="true"></i>
                Tambah produk
            </button>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 border-top pt-4">
                <div>
                    <div class="muted small">Estimasi total penjualan</div>
                    <div class="h3 fw-bold mb-1" id="sale-total">Rp 0</div>
                    <div class="muted small">
                        Harga dan ketersediaan stok diperiksa kembali saat disimpan.
                    </div>
                </div>

                <button
                    type="submit"
                    id="save-sale"
                    class="btn btn-emerald"
                    @disabled($products->isEmpty())
                >
                    <i class="fa-solid fa-check me-2" aria-hidden="true"></i>
                    Konfirmasi penjualan
                </button>
            </div>
        </form>
    </section>

    <section class="panel overflow-hidden">
        <div class="p-4 border-bottom">
            <h2 class="h5 fw-bold mb-3">Riwayat penjualan</h2>

            @include('sales.filter')
        </div>

        <div class="table-responsive">
            <table class="table" style="min-width: 750px;">
                <thead>
                    <tr>
                        <th>Transaksi</th>
                        <th>Petugas</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Rincian barang</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($sales as $sale)
                        <tr>
                            <td>
                                <div class="fw-semibold small">
                                    {{ $sale->sale_number }}
                                </div>

                                <div class="muted small mt-1">
                                    {{ $sale->sold_at->format('d/m/Y H:i') }}
                                </div>
                            </td>

                            <td>{{ $sale->user?->name ?? '—' }}</td>

                            <td class="fw-semibold text-nowrap">
                                Rp {{ number_format($sale->total_amount, 0, ',', '.') }}
                            </td>

                            <td>
                                @if ($sale->status === 'confirmed')
                                    <span class="badge-soft">Dikonfirmasi</span>
                                @else
                                    <span class="badge rounded-pill text-bg-secondary">
                                        Dibatalkan
                                    </span>
                                @endif
                            </td>

                            <td>
                                <details>
                                    <summary class="small" style="cursor: pointer;">
                                        {{ $sale->items->count() }} jenis produk
                                    </summary>

                                    <ul class="small ps-3 mt-2 mb-0">
                                        @foreach ($sale->items as $detail)
                                            <li class="mb-2">
                                                <strong>{{ $detail->product_name }}</strong>

                                                <div class="muted">
                                                    {{ $detail->quantity }} ×
                                                    Rp {{ number_format($detail->unit_price, 0, ',', '.') }}
                                                    =
                                                    Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>

                                    @if ($sale->notes)
                                        <div class="muted small mt-2">
                                            Catatan: {{ $sale->notes }}
                                        </div>
                                    @endif
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="fa-solid fa-receipt" aria-hidden="true"></i>

                                    <div class="fw-semibold">
                                        Tidak ada penjualan yang ditampilkan
                                    </div>

                                    <div class="small mt-1">
                                        Ubah atau reset filter, atau buat penjualan
                                        melalui formulir di atas.
                                    </div>

                                    <a
                                        href="{{ route('sales.index') }}"
                                        class="btn btn-light btn-sm mt-3"
                                    >
                                        Reset filter
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3 border-top">
            {{ $sales->links('pagination::bootstrap-5') }}
        </div>
    </section>

    <template id="sale-item-template">
        <tr class="sale-item">
            <td>
                <select class="form-select product-select" aria-label="Produk" required>
                    <option value="" data-price="0" data-stock="0">
                        Pilih produk
                    </option>

                    @foreach ($products as $product)
                        <option
                            value="{{ $product->id }}"
                            data-price="{{ $product->selling_price }}"
                            data-stock="{{ $product->stock }}"
                        >
                            {{ $product->sku }} — {{ $product->name }}
                        </option>
                    @endforeach
                </select>

                <div class="stock-warning text-danger small mt-2" hidden>
                    Jumlah melebihi stok yang ditampilkan.
                </div>
            </td>

            <td>
                <span class="badge-soft available-stock">0</span>
            </td>

            <td class="unit-price text-nowrap">Rp 0</td>

            <td>
                <input
                    type="number"
                    class="form-control quantity-input"
                    value="1"
                    min="1"
                    max="1000000"
                    step="1"
                    aria-label="Jumlah barang"
                    required
                >
            </td>

            <td class="item-subtotal fw-semibold text-nowrap">Rp 0</td>

            <td class="text-end">
                <button
                    type="button"
                    class="action-button danger remove-item"
                    aria-label="Hapus baris produk"
                >
                    <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                </button>
            </td>
        </tr>
    </template>

    <script>
        (() => {
            const form = document.getElementById('sale-form');
            const container = document.getElementById('sale-items');
            const template = document.getElementById('sale-item-template');
            const addButton = document.getElementById('add-item');
            const saveButton = document.getElementById('save-sale');

            const currency = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0
            });

            function updateRows() {
                let total = 0;
                const rows = container.querySelectorAll('.sale-item');

                rows.forEach((row, index) => {
                    const product = row.querySelector('.product-select');
                    const quantity = row.querySelector('.quantity-input');
                    const selected = product.selectedOptions[0];
                    const price = Number(selected?.dataset.price || 0);
                    const stock = Number(selected?.dataset.stock || 0);
                    const amount = Math.max(0, Number(quantity.value) || 0);
                    const subtotal = price * amount;

                    product.name = `items[${index}][product_id]`;
                    quantity.name = `items[${index}][quantity]`;

                    row.querySelector('.available-stock').textContent = stock;
                    row.querySelector('.unit-price').textContent = currency.format(price);
                    row.querySelector('.item-subtotal').textContent = currency.format(subtotal);
                    row.querySelector('.stock-warning').hidden = !product.value || amount <= stock;
                    row.querySelector('.remove-item').disabled = rows.length === 1;

                    total += subtotal;
                });

                addButton.disabled = rows.length >= 50;
                document.getElementById('sale-total').textContent = currency.format(total);
            }

            addButton.addEventListener('click', () => {
                if (container.children.length >= 50) {
                    return;
                }

                container.appendChild(template.content.cloneNode(true));
                updateRows();
            });

            container.addEventListener('input', updateRows);
            container.addEventListener('change', updateRows);

            container.addEventListener('click', (event) => {
                const button = event.target.closest('.remove-item');

                if (!button || container.children.length <= 1) {
                    return;
                }

                button.closest('.sale-item').remove();
                updateRows();
            });

            form.addEventListener('submit', (event) => {
                const selectedIds = Array.from(
                    container.querySelectorAll('.product-select')
                ).map(select => select.value);

                if (new Set(selectedIds).size !== selectedIds.length) {
                    event.preventDefault();
                    alert('Produk yang sama cukup dimasukkan sekali. Gabungkan jumlahnya.');
                    return;
                }

                if (!confirm('Konfirmasi penjualan ini? Stok barang akan berkurang.')) {
                    event.preventDefault();
                    return;
                }

                saveButton.disabled = true;
                saveButton.textContent = 'Menyimpan...';
            });

            window.addEventListener('pageshow', () => {
                saveButton.disabled = @json($products->isEmpty());
                saveButton.textContent = 'Konfirmasi penjualan';
            });

            updateRows();
        })();
    </script>
@endsection
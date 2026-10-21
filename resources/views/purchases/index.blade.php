@extends('layouts.app')

@section('title', 'Pembelian')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="page-title mb-2">Pembelian barang</h1>
            <p class="muted mb-0">
                Catat barang masuk dari pemasok dan perbarui persediaan.
            </p>
        </div>

        <span class="badge-soft">
            <i class="fa-solid fa-arrow-down me-1" aria-hidden="true"></i>
            Barang masuk
        </span>
    </div>

    <section class="panel p-4 mb-4">
        <div class="d-flex align-items-center gap-3 mb-4">
            <span class="brand-icon">
                <i class="fa-solid fa-cart-flatbed" aria-hidden="true"></i>
            </span>

            <div>
                <h2 class="h5 fw-bold mb-1">Pembelian baru</h2>
                <div class="muted small">
                    Pilih pemasok dan produk yang diterima.
                </div>
            </div>
        </div>

        @if ($suppliers->isEmpty() || $products->isEmpty())
            <div class="alert alert-warning">
                Pemasok dan produk harus tersedia sebelum mencatat pembelian.
                Hubungi Admin untuk melengkapi data master.
            </div>
        @endif

        <form
            action="{{ route('purchases.store') }}"
            method="POST"
            id="purchase-form"
        >
            @csrf

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="supplier_id" class="form-label">
                        Pemasok <span class="text-danger">*</span>
                    </label>

                    <select
                        id="supplier_id"
                        name="supplier_id"
                        class="form-select"
                        required
                    >
                        <option value="">Pilih pemasok</option>

                        @foreach ($suppliers as $supplier)
                            <option
                                value="{{ $supplier->id }}"
                                @selected((string) old('supplier_id') === (string) $supplier->id)
                            >
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="notes" class="form-label">Catatan</label>

                    <input
                        type="text"
                        id="notes"
                        name="notes"
                        class="form-control"
                        value="{{ old('notes') }}"
                        maxlength="1000"
                        placeholder="Contoh: Barang diterima dalam kondisi baik"
                    >
                </div>
            </div>

            <div class="table-responsive mb-3">
                <table class="table" style="min-width: 720px;">
                    <thead>
                        <tr>
                            <th style="width: 40%;">Produk</th>
                            <th>Harga beli</th>
                            <th style="width: 130px;">Jumlah</th>
                            <th>Subtotal</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>

                    <tbody id="purchase-items">
                        @foreach (old('items', [['product_id' => '', 'quantity' => 1]]) as $item)
                            <tr class="purchase-item">
                                <td>
                                    <select
                                        name="items[{{ $loop->index }}][product_id]"
                                        class="form-select product-select"
                                        aria-label="Produk"
                                        required
                                    >
                                        <option value="" data-price="0">Pilih produk</option>

                                        @foreach ($products as $product)
                                            <option
                                                value="{{ $product->id }}"
                                                data-price="{{ $product->purchase_price }}"
                                                @selected((string) ($item['product_id'] ?? '') === (string) $product->id)
                                            >
                                                {{ $product->sku }} — {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
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
                    <div class="muted small">Estimasi total pembelian</div>
                    <div class="h3 fw-bold mb-1" id="purchase-total">Rp 0</div>
                    <div class="muted small">
                        Total akhir mengikuti harga beli produk saat transaksi disimpan.
                    </div>
                </div>

                <button
                    type="submit"
                    class="btn btn-emerald"
                    id="save-purchase"
                    @disabled($suppliers->isEmpty() || $products->isEmpty())
                >
                    <i class="fa-solid fa-check me-2" aria-hidden="true"></i>
                    Konfirmasi pembelian
                </button>
            </div>
        </form>
    </section>

    <section class="panel overflow-hidden">
        <div class="p-4 border-bottom">
            <h2 class="h5 fw-bold mb-3">Riwayat pembelian</h2>

            @include('purchases.filter')
        </div>

        <div class="table-responsive">
            <table class="table" style="min-width: 800px;">
                <thead>
                    <tr>
                        <th>Transaksi</th>
                        <th>Pemasok</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Rincian barang</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($purchases as $purchase)
                        <tr>
                            <td>
                                <div class="fw-semibold small">
                                    {{ $purchase->purchase_number }}
                                </div>

                                <div class="muted small mt-1">
                                    {{ $purchase->purchased_at->format('d/m/Y H:i') }}
                                </div>

                                <div class="muted small mt-1">
                                    Oleh {{ $purchase->user?->name ?? '—' }}
                                </div>
                            </td>

                            <td>{{ $purchase->supplier?->name ?? '—' }}</td>

                            <td class="fw-semibold text-nowrap">
                                Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}
                            </td>

                            <td>
                                @if ($purchase->status === 'confirmed')
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
                                        {{ $purchase->items->count() }} jenis produk
                                    </summary>

                                    <ul class="small ps-3 mt-2 mb-0">
                                        @foreach ($purchase->items as $detail)
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

                                    @if ($purchase->notes)
                                        <div class="muted small mt-2">
                                            Catatan: {{ $purchase->notes }}
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
                                        Tidak ada pembelian yang ditampilkan
                                    </div>

                                    <div class="small mt-1">
                                        Jika filter aktif, ubah filter atau klik Reset.
                                        Pembelian baru dapat dicatat melalui formulir di atas.
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3 border-top">
            {{ $purchases->links('pagination::bootstrap-5') }}
        </div>
    </section>

    <template id="purchase-item-template">
        <tr class="purchase-item">
            <td>
                <select class="form-select product-select" aria-label="Produk" required>
                    <option value="" data-price="0">Pilih produk</option>

                    @foreach ($products as $product)
                        <option
                            value="{{ $product->id }}"
                            data-price="{{ $product->purchase_price }}"
                        >
                            {{ $product->sku }} — {{ $product->name }}
                        </option>
                    @endforeach
                </select>
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
            const form = document.getElementById('purchase-form');
            const container = document.getElementById('purchase-items');
            const template = document.getElementById('purchase-item-template');
            const addButton = document.getElementById('add-item');
            const saveButton = document.getElementById('save-purchase');

            const currency = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0
            });

            function updateRows() {
                let total = 0;
                const rows = container.querySelectorAll('.purchase-item');

                rows.forEach((row, index) => {
                    const product = row.querySelector('.product-select');
                    const quantity = row.querySelector('.quantity-input');
                    const price = Number(product.selectedOptions[0]?.dataset.price || 0);
                    const amount = Math.max(0, Number(quantity.value) || 0);
                    const subtotal = price * amount;

                    product.name = `items[${index}][product_id]`;
                    quantity.name = `items[${index}][quantity]`;

                    row.querySelector('.unit-price').textContent = currency.format(price);
                    row.querySelector('.item-subtotal').textContent = currency.format(subtotal);
                    row.querySelector('.remove-item').disabled = rows.length === 1;

                    total += subtotal;
                });

                addButton.disabled = rows.length >= 50;
                document.getElementById('purchase-total').textContent = currency.format(total);
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

                button.closest('.purchase-item').remove();
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

                if (!confirm('Konfirmasi pembelian ini? Stok barang akan bertambah.')) {
                    event.preventDefault();
                    return;
                }

                saveButton.disabled = true;
                saveButton.textContent = 'Menyimpan...';
            });

            window.addEventListener('pageshow', () => {
                saveButton.disabled = @json($suppliers->isEmpty() || $products->isEmpty());
                saveButton.textContent = 'Konfirmasi pembelian';
            });

            updateRows();
        })();
    </script>
@endsection
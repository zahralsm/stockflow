<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(
            $request->user()->isAdmin() || $request->user()->isStaff(),
            403
        );

        $filters = $request->validate(
            [
                'q' => ['nullable', 'string', 'max:100'],
                'start_date' => ['nullable', 'date_format:Y-m-d'],
                'end_date' => ['nullable', 'date_format:Y-m-d'],
                'status' => ['nullable', 'in:confirmed,cancelled'],
            ],
            [
                'start_date.date_format' => 'Tanggal awal tidak valid.',
                'end_date.date_format' => 'Tanggal akhir tidak valid.',
                'status.in' => 'Status transaksi tidak valid.',
                'q.max' => 'Pencarian maksimal 100 karakter.',
            ]
        );

        $search = trim($filters['q'] ?? '');
        $startDate = $filters['start_date'] ?? '';
        $endDate = $filters['end_date'] ?? '';
        $status = $filters['status'] ?? '';

        if (
            $startDate !== ''
            && $endDate !== ''
            && $endDate < $startDate
        ) {
            throw ValidationException::withMessages([
                'end_date' => 'Tanggal akhir harus sama dengan atau setelah tanggal awal.',
            ]);
        }

        $query = Purchase::query()
            ->with(['supplier', 'user', 'items']);

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('purchase_number', 'like', '%' . $search . '%')
                    ->orWhereHas('supplier', function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($startDate !== '') {
            $query->where(
                'purchased_at',
                '>=',
                Carbon::parse($startDate)->startOfDay()
            );
        }

        if ($endDate !== '') {
            $query->where(
                'purchased_at',
                '<',
                Carbon::parse($endDate)->startOfDay()->addDay()
            );
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        $purchases = $query
            ->orderByDesc('purchased_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('purchases.index', [
            'purchases' => $purchases,
            'suppliers' => Supplier::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
            'search' => $search,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'status' => $status,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(
            $request->user()->isAdmin() || $request->user()->isStaff(),
            403
        );

        $data = $request->validate(
            [
                'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
                'notes' => ['nullable', 'string', 'max:1000'],
                'items' => ['required', 'array', 'min:1', 'max:50'],
                'items.*' => ['required', 'array:product_id,quantity'],
                'items.*.product_id' => [
                    'required',
                    'integer',
                    'distinct',
                    'exists:products,id',
                ],
                'items.*.quantity' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:1000000',
                ],
            ],
            [
                'supplier_id.required' => 'Pilih pemasok terlebih dahulu.',
                'supplier_id.exists' => 'Pemasok yang dipilih tidak ditemukan.',
                'notes.max' => 'Catatan maksimal 1.000 karakter.',
                'items.required' => 'Tambahkan minimal satu produk.',
                'items.min' => 'Tambahkan minimal satu produk.',
                'items.max' => 'Maksimal 50 baris produk per transaksi.',
                'items.*.product_id.required' => 'Pilih produk pada setiap baris.',
                'items.*.product_id.exists' => 'Salah satu produk tidak ditemukan.',
                'items.*.product_id.distinct' => 'Produk yang sama cukup dimasukkan sekali. Gabungkan jumlahnya.',
                'items.*.quantity.required' => 'Jumlah barang wajib diisi.',
                'items.*.quantity.integer' => 'Jumlah barang harus berupa bilangan bulat.',
                'items.*.quantity.min' => 'Jumlah barang minimal 1.',
                'items.*.quantity.max' => 'Jumlah barang maksimal 1.000.000 per produk.',
            ]
        );

        $purchase = DB::transaction(function () use ($data, $request) {
            $supplier = Supplier::query()
                ->lockForUpdate()
                ->find($data['supplier_id']);

            if ($supplier === null) {
                throw ValidationException::withMessages([
                    'supplier_id' => 'Pemasok sudah tidak tersedia. Pilih pemasok lain.',
                ]);
            }

            $productIds = collect($data['items'])
                ->pluck('product_id')
                ->map(fn ($id) => (int) $id)
                ->sort()
                ->values()
                ->all();

            $products = Product::query()
                ->whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $details = [];
            $total = 0;

            foreach ($data['items'] as $index => $item) {
                $product = $products->get((int) $item['product_id']);

                if ($product === null) {
                    throw ValidationException::withMessages([
                        "items.$index.product_id" => 'Produk sudah tidak tersedia. Muat ulang halaman.',
                    ]);
                }

                $quantity = (int) $item['quantity'];

                if ((int) $product->stock + $quantity > 4294967295) {
                    throw ValidationException::withMessages([
                        "items.$index.quantity" => "Penambahan stok {$product->name} melebihi kapasitas penyimpanan.",
                    ]);
                }

                $unitPrice = (int) $product->purchase_price;
                $subtotal = $unitPrice * $quantity;
                $total += $subtotal;

                $details[] = [
                    'product_id' => $product->id,
                    'product_sku' => $product->sku,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ];
            }

            $purchase = Purchase::create([
                'purchase_number' => 'PUR-' . Str::ulid(),
                'supplier_id' => $supplier->id,
                'user_id' => $request->user()->id,
                'purchased_at' => now(),
                'total_amount' => $total,
                'status' => 'confirmed',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($details as $detail) {
                $purchase->items()->create($detail);

                $products->get($detail['product_id'])
                    ->increment('stock', $detail['quantity']);
            }

            return $purchase;
        }, 3);

        return redirect()
            ->route('purchases.index')
            ->with(
                'success',
                "Pembelian {$purchase->purchase_number} berhasil disimpan. Stok barang sudah bertambah."
            );
    }
}
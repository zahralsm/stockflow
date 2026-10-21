<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SaleController extends Controller
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

        $query = Sale::query()->with(['user', 'items']);

        if ($search !== '') {
            $query->where('sale_number', 'like', '%' . $search . '%');
        }

        if ($startDate !== '') {
            $query->where(
                'sold_at',
                '>=',
                Carbon::parse($startDate)->startOfDay()
            );
        }

        if ($endDate !== '') {
            $query->where(
                'sold_at',
                '<',
                Carbon::parse($endDate)->startOfDay()->addDay()
            );
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        $sales = $query
            ->orderByDesc('sold_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('sales.index', [
            'sales' => $sales,
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

        $sale = DB::transaction(function () use ($data, $request) {
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

                if ((int) $product->stock < $quantity) {
                    throw ValidationException::withMessages([
                        "items.$index.quantity" =>
                            "Stok {$product->name} tidak cukup. Tersedia {$product->stock}, diminta {$quantity}. Seluruh penjualan dibatalkan.",
                    ]);
                }

                $unitPrice = (int) $product->selling_price;
                $unitCost = (int) $product->purchase_price;
                $subtotal = $unitPrice * $quantity;

                $total += $subtotal;

                $details[] = [
                    'product_id' => $product->id,
                    'product_sku' => $product->sku,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                ];
            }

            $sale = Sale::create([
                'sale_number' => 'SAL-' . Str::ulid(),
                'user_id' => $request->user()->id,
                'sold_at' => now(),
                'total_amount' => $total,
                'status' => 'confirmed',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($details as $detail) {
                $sale->items()->create($detail);

                $products->get($detail['product_id'])
                    ->decrement('stock', $detail['quantity']);
            }

            return $sale;
        }, 3);

        return redirect()
            ->route('sales.index')
            ->with(
                'success',
                "Penjualan {$sale->sale_number} berhasil disimpan. Stok barang sudah berkurang."
            );
    }
}
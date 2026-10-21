<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CancellationController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->isAdmin(), 403);

        $filters = $request->validate([
            'type' => ['nullable', 'in:sales,purchases'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $type = $filters['type'] ?? 'sales';
        $search = trim($filters['q'] ?? '');

        $query = $type === 'sales'
            ? Sale::query()->with(['user', 'items'])
            : Purchase::query()->with(['user', 'supplier', 'items']);

        $numberColumn = $type === 'sales'
            ? 'sale_number'
            : 'purchase_number';

        $transactions = $query
            ->when($search !== '', function ($query) use ($numberColumn, $search) {
                $query->where($numberColumn, 'like', '%' . $search . '%');
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('cancellations.index', [
            'transactions' => $transactions,
            'type' => $type,
            'search' => $search,
        ]);
    }

    public function cancelSale(Request $request, Sale $sale): RedirectResponse
    {
        return $this->cancelTransaction($request, $sale, true);
    }

    public function cancelPurchase(
        Request $request,
        Purchase $purchase
    ): RedirectResponse {
        return $this->cancelTransaction($request, $purchase, false);
    }

    private function cancelTransaction(
        Request $request,
        Sale|Purchase $transaction,
        bool $isSale
    ): RedirectResponse {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate(
            [
                'cancellation_reason' => [
                    'required',
                    'string',
                    'min:5',
                    'max:1000',
                ],
            ],
            [
                'cancellation_reason.required' => 'Alasan pembatalan wajib diisi.',
                'cancellation_reason.min' => 'Alasan pembatalan minimal 5 karakter.',
                'cancellation_reason.max' => 'Alasan pembatalan maksimal 1.000 karakter.',
            ]
        );

        DB::transaction(function () use (
            $request,
            $transaction,
            $isSale,
            $data
        ) {
            $lockedTransaction = $transaction->newQuery()
                ->lockForUpdate()
                ->findOrFail($transaction->id);

            if ($lockedTransaction->status !== 'confirmed') {
                throw ValidationException::withMessages([
                    'transaction' => 'Transaksi ini sudah dibatalkan. Stok tidak diubah lagi.',
                ]);
            }

            $items = $lockedTransaction->items()
                ->orderBy('product_id')
                ->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'transaction' => 'Transaksi tidak memiliki detail barang. Pembatalan tidak dapat diproses.',
                ]);
            }

            $products = Product::query()
                ->whereIn('id', $items->pluck('product_id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($items as $item) {
                $product = $products->get($item->product_id);

                if (!$product) {
                    throw ValidationException::withMessages([
                        'transaction' => "Produk {$item->product_name} tidak ditemukan.",
                    ]);
                }

                if (!$isSale && $product->stock < $item->quantity) {
                    throw ValidationException::withMessages([
                        'transaction' =>
                            "Pembelian tidak dapat dibatalkan. Stok {$product->name} hanya {$product->stock}, sedangkan pembatalan membutuhkan {$item->quantity}.",
                    ]);
                }

                if ($isSale && $product->stock + $item->quantity > 4294967295) {
                    throw ValidationException::withMessages([
                        'transaction' =>
                            "Pengembalian stok {$product->name} melebihi kapasitas penyimpanan.",
                    ]);
                }
            }

            foreach ($items as $item) {
                $product = $products->get($item->product_id);

                if ($isSale) {
                    $product->increment('stock', $item->quantity);
                } else {
                    $product->decrement('stock', $item->quantity);
                }
            }

            $lockedTransaction->status = 'cancelled';
            $lockedTransaction->cancelled_by = $request->user()->id;
            $lockedTransaction->cancelled_at = now();
            $lockedTransaction->cancellation_reason = $data['cancellation_reason'];
            $lockedTransaction->save();
        }, 3);

        return redirect()
            ->route('cancellations.index', [
                'type' => $isSale ? 'sales' : 'purchases',
            ])
            ->with(
                'success',
                'Transaksi berhasil dibatalkan dan stok sudah disesuaikan.'
            );
    }
}
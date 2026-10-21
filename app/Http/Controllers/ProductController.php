<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'category_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
            ],
            'stock_status' => [
                'nullable',
                Rule::in(['low', 'safe']),
            ],
            'edit' => ['nullable', 'integer', 'min:1'],
        ]);

        $search = trim($filters['q'] ?? '');
        $categoryId = $filters['category_id'] ?? null;
        $stockStatus = $filters['stock_status'] ?? null;
        $editingProduct = null;

        if (isset($filters['edit'])) {
            abort_unless($request->user()->isAdmin(), 403);

            $editingProduct = Product::findOrFail($filters['edit']);
        }

        $products = Product::query()
            ->with('category')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('sku', 'like', '%'.$search.'%')
                        ->orWhere('name', 'like', '%'.$search.'%');
                });
            })
            ->when($categoryId, function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($stockStatus === 'low', function ($query) {
                $query->whereColumn('stock', '<=', 'minimum_stock');
            })
            ->when($stockStatus === 'safe', function ($query) {
                $query->whereColumn('stock', '>', 'minimum_stock');
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
            'editingProduct' => $editingProduct,
            'search' => $search,
            'categoryId' => $categoryId,
            'stockStatus' => $stockStatus,
            'totalProducts' => Product::count(),
            'lowStockCount' => Product::whereColumn(
                'stock',
                '<=',
                'minimum_stock'
            )->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateProduct($request);

        Product::create($data);

        return redirect()
            ->route('products.index')
            ->with(
                'success',
                'Produk berhasil ditambahkan. Stok awal 0; isi stok melalui pembelian.'
            );
    }

    public function update(
        Request $request,
        Product $product
    ): RedirectResponse {
        $data = $this->validateProduct($request, $product);

        $product->update($data);

        return redirect()
            ->route('products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        try {
            DB::transaction(function () use ($product) {
                $lockedProduct = Product::whereKey($product->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedProduct->stock > 0) {
                    throw ValidationException::withMessages([
                        'product' => 'Produk masih memiliki stok dan tidak dapat dihapus.',
                    ]);
                }

                if (
                    $lockedProduct->purchaseItems()->exists()
                    || $lockedProduct->saleItems()->exists()
                ) {
                    throw ValidationException::withMessages([
                        'product' => 'Produk memiliki riwayat transaksi dan tidak dapat dihapus.',
                    ]);
                }

                $lockedProduct->delete();
            }, 3);
        } catch (QueryException $exception) {
            if (($exception->errorInfo[0] ?? null) !== '23000') {
                throw $exception;
            }

            throw ValidationException::withMessages([
                'product' => 'Produk masih digunakan oleh data lain dan tidak dapat dihapus.',
            ]);
        }

        return redirect()
            ->route('products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    private function validateProduct(
        Request $request,
        ?Product $product = null
    ): array {
        if (is_string($request->input('sku'))) {
            $request->merge([
                'sku' => Str::upper(trim($request->input('sku'))),
            ]);
        }

        $data = $request->validate(
            [
                'category_id' => [
                    'required',
                    'integer',
                    'exists:categories,id',
                ],
                'sku' => [
                    'required',
                    'string',
                    'max:50',
                    'regex:/^[A-Z0-9][A-Z0-9_-]*$/',
                    Rule::unique('products', 'sku')
                        ->ignore($product?->id),
                ],
                'name' => [
                    'required',
                    'string',
                    'max:150',
                ],
                'purchase_price' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:1000000000',
                ],
                'selling_price' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:1000000000',
                ],
                'minimum_stock' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:1000000',
                ],
                'stock' => ['prohibited'],
            ],
            [
                'required' => ':attribute wajib diisi.',
                'string' => ':attribute harus berupa teks.',
                'integer' => ':attribute harus berupa bilangan bulat.',
                'min' => ':attribute minimal :min.',
                'max' => ':attribute melebihi batas maksimal :max.',
                'category_id.exists' => 'Kategori yang dipilih tidak tersedia.',
                'sku.unique' => 'SKU sudah digunakan oleh produk lain.',
                'sku.regex' => 'SKU hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah; harus diawali huruf atau angka.',
                'stock.prohibited' => 'Stok hanya boleh diubah melalui transaksi persediaan.',
            ],
            [
                'category_id' => 'Kategori',
                'sku' => 'SKU',
                'name' => 'Nama produk',
                'purchase_price' => 'Harga beli',
                'selling_price' => 'Harga jual',
                'minimum_stock' => 'Stok minimum',
            ]
        );

        unset($data['stock']);

        return $data;
    }
}
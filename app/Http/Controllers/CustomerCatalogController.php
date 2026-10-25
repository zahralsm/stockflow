<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class CustomerCatalogController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil daftar kategori buat tombol filter
        $categories = Category::all();

        // 2. Query produk yang stoknya masih ada (stok > 0)
        $query = Product::with('category')->where('stock', '>', 0);

        // Fitur pencarian nama produk
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Fitur filter berdasarkan kategori
        if ($request->has('category') && $request->category != '') {
            $query->where('category_id', $request->category);
        }

        // Ambil data produk (pakai paginate biar rapi 12 barang per halaman)
        $products = $query->latest()->paginate(12);

        // 3. Lempar ke tampilan view katalog customer
        return view('customer.catalog', compact('products', 'categories'));
    }
}
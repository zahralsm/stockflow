<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'edit' => ['nullable', 'integer', 'min:1'],
        ]);

        $search = trim($filters['q'] ?? '');

        $categories = Category::query()
            ->withCount('products')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $editingCategory = isset($filters['edit'])
            ? Category::findOrFail($filters['edit'])
            : null;

        return view('categories.index', [
            'categories' => $categories,
            'editingCategory' => $editingCategory,
            'search' => $search,
            'totalCategories' => Category::count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('categories', 'name'),
                ],
            ],
            [
                'name.required' => 'Nama kategori wajib diisi.',
                'name.string' => 'Nama kategori harus berupa teks.',
                'name.max' => 'Nama kategori maksimal 100 karakter.',
                'name.unique' => 'Nama kategori sudah digunakan.',
            ]
        );

        Category::create($data);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(
        Request $request,
        Category $category
    ): RedirectResponse {
        $data = $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('categories', 'name')->ignore($category),
                ],
            ],
            [
                'name.required' => 'Nama kategori wajib diisi.',
                'name.string' => 'Nama kategori harus berupa teks.',
                'name.max' => 'Nama kategori maksimal 100 karakter.',
                'name.unique' => 'Nama kategori sudah digunakan.',
            ]
        );

        $category->update($data);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->withErrors([
                'category' => 'Kategori tidak dapat dihapus karena masih digunakan oleh produk.',
            ]);
        }

        try {
            $category->delete();
        } catch (QueryException $exception) {
            if (($exception->errorInfo[0] ?? null) !== '23000') {
                throw $exception;
            }

            return back()->withErrors([
                'category' => 'Kategori masih digunakan oleh data lain dan tidak dapat dihapus.',
            ]);
        }

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
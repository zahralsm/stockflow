<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'edit' => ['nullable', 'integer', 'min:1'],
        ]);

        $search = trim($filters['q'] ?? '');

        $editingSupplier = !empty($filters['edit'])
            ? Supplier::findOrFail($filters['edit'])
            : null;

        $suppliers = Supplier::query()
            ->withCount('purchases')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('suppliers.index', [
            'suppliers' => $suppliers,
            'editingSupplier' => $editingSupplier,
            'search' => $search,
            'totalSuppliers' => Supplier::count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateSupplier($request);

        Supplier::create($data);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Pemasok berhasil ditambahkan.');
    }

    public function update(
        Request $request,
        Supplier $supplier
    ): RedirectResponse {
        $data = $this->validateSupplier($request);

        $supplier->update($data);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Data pemasok berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        try {
            DB::transaction(function () use ($supplier) {
                $lockedSupplier = Supplier::query()
                    ->lockForUpdate()
                    ->findOrFail($supplier->id);

                if ($lockedSupplier->purchases()->exists()) {
                    throw ValidationException::withMessages([
                        'supplier' => 'Pemasok tidak dapat dihapus karena sudah memiliki riwayat pembelian.',
                    ]);
                }

                $lockedSupplier->delete();
            }, 3);
        } catch (QueryException $exception) {
            if ((string) $exception->getCode() === '23000') {
                throw ValidationException::withMessages([
                    'supplier' => 'Pemasok tidak dapat dihapus karena masih digunakan oleh data lain.',
                ]);
            }

            throw $exception;
        }

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Pemasok berhasil dihapus.');
    }

    private function validateSupplier(Request $request): array
    {
        return $request->validate(
            [
                'name' => ['required', 'string', 'max:150'],
                'phone' => [
                    'nullable',
                    'string',
                    'max:25',
                    'regex:/^[0-9+().\s-]+$/',
                ],
                'email' => ['nullable', 'email', 'max:254'],
            ],
            [
                'name.required' => 'Nama pemasok wajib diisi.',
                'name.string' => 'Nama pemasok harus berupa teks.',
                'name.max' => 'Nama pemasok maksimal 150 karakter.',
                'phone.string' => 'Nomor telepon harus berupa teks.',
                'phone.max' => 'Nomor telepon maksimal 25 karakter.',
                'phone.regex' => 'Format nomor telepon tidak valid.',
                'email.email' => 'Masukkan alamat email yang valid.',
                'email.max' => 'Alamat email maksimal 254 karakter.',
            ]
        );
    }
}
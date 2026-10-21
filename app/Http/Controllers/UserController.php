<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->isAdmin(), 403);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'edit' => ['nullable', 'integer', 'min:1'],
        ]);

        $search = trim($filters['q'] ?? '');

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('username', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        $editingUser = null;

        if (!empty($filters['edit'])) {
            $editingUser = User::findOrFail($filters['edit']);
        }

        return view('users.index', [
            'users' => $users,
            'search' => $search,
            'editingUser' => $editingUser,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $this->validateUser($request);

        $user = new User();
        $user->name = $data['name'];
        $user->username = $data['username'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        $user->password = Hash::make($data['password']);
        $user->save();

        return redirect()
            ->route('users.index')
            ->with('success', 'Akun baru berhasil dibuat.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $this->validateUser($request, $user);

        DB::transaction(function () use ($request, $user, $data) {
            $lockedUsers = User::query()
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $target = $lockedUsers->firstWhere('id', $user->id);

            if ($target === null) {
                abort(404, 'Akun tidak ditemukan.');
            }

            $actor = $lockedUsers->firstWhere(
                'id',
                $request->user()->id
            );

            if ($actor === null || $actor->role !== 'admin') {
                abort(403, 'Hanya Admin yang dapat mengelola akun.');
            }

            if (
                $target->id === $actor->id
                && $data['role'] !== 'admin'
            ) {
                throw ValidationException::withMessages([
                    'role' => 'Role akun yang sedang digunakan tidak boleh diubah menjadi Staff.',
                ]);
            }

            $adminCount = $lockedUsers
                ->where('role', 'admin')
                ->count();

            if (
                $target->role === 'admin'
                && $data['role'] !== 'admin'
                && $adminCount <= 1
            ) {
                throw ValidationException::withMessages([
                    'role' => 'Aplikasi harus memiliki minimal satu Admin.',
                ]);
            }

            $target->name = $data['name'];
            $target->username = $data['username'];
            $target->email = $data['email'];
            $target->role = $data['role'];

            if (!empty($data['password'])) {
                $target->password = Hash::make($data['password']);
            }

            $target->save();
        }, 3);

        return redirect()
            ->route('users.index')
            ->with('success', 'Data akun berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        try {
            DB::transaction(function () use ($request, $user) {
                $lockedUsers = User::query()
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                $target = $lockedUsers->firstWhere('id', $user->id);

                if ($target === null) {
                    abort(404, 'Akun tidak ditemukan.');
                }

                $actor = $lockedUsers->firstWhere(
                    'id',
                    $request->user()->id
                );

                if ($actor === null || $actor->role !== 'admin') {
                    abort(403, 'Hanya Admin yang dapat mengelola akun.');
                }

                if ($target->id === $actor->id) {
                    throw ValidationException::withMessages([
                        'user' => 'Akun yang sedang digunakan tidak boleh dihapus.',
                    ]);
                }

                $adminCount = $lockedUsers
                    ->where('role', 'admin')
                    ->count();

                if ($target->role === 'admin' && $adminCount <= 1) {
                    throw ValidationException::withMessages([
                        'user' => 'Admin terakhir tidak boleh dihapus.',
                    ]);
                }

                $hasPurchaseHistory = DB::table('purchases')
                    ->where(function ($query) use ($target) {
                        $query->where('user_id', $target->id)
                            ->orWhere('cancelled_by', $target->id);
                    })
                    ->exists();

                $hasSaleHistory = DB::table('sales')
                    ->where(function ($query) use ($target) {
                        $query->where('user_id', $target->id)
                            ->orWhere('cancelled_by', $target->id);
                    })
                    ->exists();

                if ($hasPurchaseHistory || $hasSaleHistory) {
                    throw ValidationException::withMessages([
                        'user' => 'Akun memiliki riwayat transaksi atau pembatalan sehingga tidak dapat dihapus.',
                    ]);
                }

                $target->delete();
            }, 3);
        } catch (QueryException $exception) {
            if ((string) $exception->getCode() === '23000') {
                throw ValidationException::withMessages([
                    'user' => 'Akun masih digunakan oleh data lain dan tidak dapat dihapus.',
                ]);
            }

            throw $exception;
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'Akun berhasil dihapus.');
    }

    private function validateUser(
        Request $request,
        ?User $user = null
    ): array {
        return $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'max:100',
                ],
                'username' => [
                    'required',
                    'string',
                    'max:50',
                    'regex:/^[a-zA-Z0-9_.-]+$/',
                    Rule::unique('users', 'username')->ignore($user?->id),
                ],
                'email' => [
                    'required',
                    'email',
                    'max:254',
                    Rule::unique('users', 'email')->ignore($user?->id),
                ],
                'role' => [
                    'required',
                    Rule::in(['admin', 'staff']),
                ],
                'password' => [
                    $user === null ? 'required' : 'nullable',
                    'string',
                    'min:8',
                    'max:72',
                    'confirmed',
                ],
            ],
            [
                'name.required' => 'Nama wajib diisi.',
                'name.string' => 'Nama harus berupa teks.',
                'name.max' => 'Nama maksimal 100 karakter.',
                'username.required' => 'Username wajib diisi.',
                'username.string' => 'Username harus berupa teks.',
                'username.max' => 'Username maksimal 50 karakter.',
                'username.unique' => 'Username sudah digunakan.',
                'username.regex' => 'Username hanya boleh berisi huruf, angka, titik, garis bawah, dan tanda minus.',
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'email.max' => 'Email maksimal 254 karakter.',
                'email.unique' => 'Email sudah digunakan.',
                'role.required' => 'Pilih role akun.',
                'role.in' => 'Role akun tidak valid.',
                'password.required' => 'Password wajib diisi untuk akun baru.',
                'password.string' => 'Password harus berupa teks.',
                'password.min' => 'Password minimal 8 karakter.',
                'password.max' => 'Password maksimal 72 karakter.',
                'password.confirmed' => 'Konfirmasi password tidak cocok.',
            ]
        );
    }
}
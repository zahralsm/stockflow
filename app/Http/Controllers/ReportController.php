<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->isAdmin(), 403);

        $filters = $request->validate(
            [
                'type' => ['nullable', 'in:sales,purchases'],
                'start_date' => ['nullable', 'date_format:Y-m-d'],
                'end_date' => ['nullable', 'date_format:Y-m-d'],
                'status' => ['nullable', 'in:confirmed,cancelled'],
                'q' => ['nullable', 'string', 'max:100'],
            ],
            [
                'start_date.date_format' => 'Format tanggal awal tidak valid.',
                'end_date.date_format' => 'Format tanggal akhir tidak valid.',
                'type.in' => 'Jenis laporan tidak valid.',
                'status.in' => 'Status transaksi tidak valid.',
                'q.max' => 'Pencarian maksimal 100 karakter.',
            ]
        );

        $type = $filters['type'] ?? 'sales';
        $startDate = $filters['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $filters['end_date'] ?? now()->toDateString();
        $status = $filters['status'] ?? '';
        $search = trim($filters['q'] ?? '');

        if ($endDate < $startDate) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'end_date' => 'Tanggal akhir harus sama dengan atau setelah tanggal awal.',
            ]);
        }

        $start = Carbon::parse($startDate)->startOfDay();
        $endExclusive = Carbon::parse($endDate)->startOfDay()->addDay();

        $isSales = $type === 'sales';

        $query = $isSales
            ? Sale::query()->with('user')
            : Purchase::query()->with(['user', 'supplier']);

        $dateColumn = $isSales ? 'sold_at' : 'purchased_at';
        $numberColumn = $isSales ? 'sale_number' : 'purchase_number';

        $query
            ->where($dateColumn, '>=', $start)
            ->where($dateColumn, '<', $endExclusive)
            ->when($status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($search !== '', function ($query) use ($numberColumn, $search) {
                $query->where($numberColumn, 'like', '%' . $search . '%');
            });

        $totalTransactions = (clone $query)->count();

        $confirmedAmount = (clone $query)
            ->where('status', 'confirmed')
            ->sum('total_amount');

        $cancelledCount = (clone $query)
            ->where('status', 'cancelled')
            ->count();

        $transactions = $query
            ->orderByDesc($dateColumn)
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('reports.index', [
            'transactions' => $transactions,
            'type' => $type,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'status' => $status,
            'search' => $search,
            'totalTransactions' => $totalTransactions,
            'confirmedAmount' => $confirmedAmount,
            'cancelledCount' => $cancelledCount,
        ]);
    }
}
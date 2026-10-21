<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $now = now();

        $todayStart = $now->copy()->startOfDay();
        $tomorrowStart = $todayStart->copy()->addDay();

        $monthStart = $now->copy()->startOfMonth();
        $nextMonthStart = $monthStart->copy()->addMonth();

        $todaySales = Sale::query()
            ->where('status', 'confirmed')
            ->where('sold_at', '>=', $todayStart)
            ->where('sold_at', '<', $tomorrowStart)
            ->sum('total_amount');

        $monthSales = Sale::query()
            ->where('status', 'confirmed')
            ->where('sold_at', '>=', $monthStart)
            ->where('sold_at', '<', $nextMonthStart)
            ->sum('total_amount');

        $lowStockQuery = Product::query()
            ->whereColumn('stock', '<=', 'minimum_stock');

        return view('dashboard', [
            'totalProducts' => Product::count(),
            'todaySales' => $todaySales,
            'monthSales' => $monthSales,
            'totalTransactions' => Purchase::count() + Sale::count(),
            'lowStockCount' => (clone $lowStockQuery)->count(),
            'lowStockProducts' => $lowStockQuery
                ->with('category')
                ->orderBy('stock')
                ->orderBy('name')
                ->limit(8)
                ->get(),
            'latestSales' => Sale::query()
                ->with('user')
                ->orderByDesc('sold_at')
                ->orderByDesc('id')
                ->limit(5)
                ->get(),
        ]);
    }
}
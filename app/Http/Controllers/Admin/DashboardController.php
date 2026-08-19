<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\NoestService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __invoke(NoestService $noest): JsonResponse
    {
        $orders = Order::query();
        $product = Product::with('variants')->where('slug', 'charm-tote')->first();

        return response()->json([
            'total_revenue' => (clone $orders)->where('status', 'delivered')->sum('total'),
            'total_orders' => (clone $orders)->count(),
            'today_orders' => (clone $orders)->whereDate('created_at', today())->count(),
            'pending_orders' => (clone $orders)->where('status', 'pending')->count(),
            'shipped_orders' => (clone $orders)->where('status', 'shipped')->count(),
            'delivered_orders' => (clone $orders)->where('status', 'delivered')->count(),
            'cancelled_orders' => (clone $orders)->whereIn('status', ['cancelled', 'returned'])->count(),
            'total_stock' => $product?->variants->sum('stock') ?? 0,
            'noest_configured' => $noest->configured(),
            'recent_orders' => Order::latest()->limit(6)->get()->map(fn (Order $order) => OrderController::serialize($order)),
        ]);
    }
}

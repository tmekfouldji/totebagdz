<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Services\NoestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    private const STATUSES = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled', 'returned'];

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', Rule::in(self::STATUSES)],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $orders = Order::latest()
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($validated['search'] ?? null, function ($query, $search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('order_number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('wilaya_name', 'like', "%{$search}%");
                });
            })->paginate(12);

        return response()->json([
            'data' => collect($orders->items())->map(fn (Order $order) => self::serialize($order)),
            'meta' => ['page' => $orders->currentPage(), 'last_page' => $orders->lastPage(), 'total' => $orders->total()],
        ]);
    }

    public function show(Order $order): JsonResponse
    {
        return response()->json(['data' => self::serialize($order->load('history'))]);
    }

    public function update(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(self::STATUSES)],
            'payment_status' => ['required', Rule::in(['pending', 'paid', 'refunded'])],
            'customer_name' => ['required', 'string', 'min:3', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'min:5', 'max:255'],
            'wilaya_name' => ['required', 'string', 'max:100'],
            'commune' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($order, $validated) {
            $this->syncStockForStatus($order, $validated['status']);
            $changes = array_diff_assoc($validated, $order->only(array_keys($validated)));
            $order->update($validated);
            $order->history()->create(['action' => 'order_updated', 'meta' => ['changes' => $changes]]);
        });

        return response()->json(['message' => 'تم حفظ التعديلات.', 'data' => self::serialize($order->fresh()->load('history'))]);
    }

    public function destroy(Order $order): JsonResponse
    {
        if ($order->noest_tracking) {
            return response()->json(['message' => 'لا يمكن حذف طلب أُرسل إلى NOEST. ألغِه بدلًا من ذلك.'], 422);
        }

        DB::transaction(function () use ($order) {
            if (! $order->stock_restored) {
                ProductVariant::whereKey($order->product_variant_id)->increment('stock', $order->quantity);
            }
            $order->delete();
        });

        return response()->json(['message' => 'تم حذف الطلب وإرجاع الكمية إلى المخزون.']);
    }

    public function dispatch(Order $order, NoestService $noest): JsonResponse
    {
        if (in_array($order->status, ['cancelled', 'returned', 'delivered'], true)) {
            return response()->json(['message' => 'لا يمكن إرسال طلب ملغى أو مرتجع أو مسلّم.'], 422);
        }

        try {
            $order = $noest->dispatch($order);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'تعذّر إرسال الطلب إلى NOEST: '.$exception->getMessage()], 422);
        }

        return response()->json(['message' => 'تم إرسال الطلب إلى NOEST.', 'data' => self::serialize($order)]);
    }

    public function export(Request $request): StreamedResponse
    {
        return response()->streamDownload(function () {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['رقم الطلب', 'التاريخ', 'الزبون', 'الهاتف', 'الولاية', 'البلدية', 'اللون', 'الكمية', 'التوصيل', 'المجموع', 'الحالة', 'تتبع NOEST']);
            Order::latest()->chunk(200, function ($orders) use ($output) {
                foreach ($orders as $order) {
                    fputcsv($output, [$order->order_number, $order->created_at, $order->customer_name, $order->phone, $order->wilaya_name, $order->commune, $order->color, $order->quantity, $order->shipping_fee, $order->total, $order->status, $order->noest_tracking]);
                }
            });
            fclose($output);
        }, 'charm-orders-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function syncStockForStatus(Order $order, string $newStatus): void
    {
        if (! $order->inventory_tracked) {
            return;
        }

        $terminal = in_array($newStatus, ['cancelled', 'returned'], true);
        if ($terminal && ! $order->stock_restored) {
            ProductVariant::whereKey($order->product_variant_id)->increment('stock', $order->quantity);
            $order->stock_restored = true;
        } elseif (! $terminal && $order->stock_restored) {
            $variant = ProductVariant::lockForUpdate()->findOrFail($order->product_variant_id);
            abort_if($variant->stock < $order->quantity, 422, 'لا توجد كمية كافية لإعادة تفعيل الطلب.');
            $variant->decrement('stock', $order->quantity);
            $order->stock_restored = false;
        }
    }

    public static function serialize(Order $order): array
    {
        return [
            ...$order->only(['id', 'order_number', 'status', 'payment_status', 'product_name', 'color', 'quantity', 'unit_price', 'subtotal', 'shipping_fee', 'total', 'customer_name', 'phone', 'wilaya_id', 'wilaya_name', 'commune', 'address', 'notes', 'delivery_type', 'payment_method']),
            'created_at' => $order->created_at?->toIso8601String(),
            'updated_at' => $order->updated_at?->toIso8601String(),
            'noest' => ['dispatched' => filled($order->noest_tracking), 'tracking' => $order->noest_tracking, 'dispatched_at' => $order->noest_dispatched_at?->toIso8601String()],
            'history' => $order->relationLoaded('history') ? $order->history->map(fn ($entry) => ['action' => $entry->action, 'at' => $entry->created_at?->toIso8601String(), ...($entry->meta ?? [])])->values() : [],
        ];
    }
}

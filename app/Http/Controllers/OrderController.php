<?php

namespace App\Http\Controllers;

use App\Models\Commune;
use App\Models\Order;
use App\Models\Product;
use App\Models\Wilaya;
use App\Services\NoestService;
use App\Services\ShippingFeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function __construct(
        private ShippingFeeService $shippingFees,
        private NoestService $noest,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $request->merge(['phone' => preg_replace('/\s+/', '', (string) $request->input('phone'))]);
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'min:3', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^(?:(?:\+|00)213|0)[5-7]\d{8}$/'],
            'wilaya_id' => ['required', 'integer', Rule::exists('wilayas', 'id')],
            'commune' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'min:5', 'max:255'],
            'color' => ['required', 'string', Rule::in(['أسود', 'بني', 'أخضر', 'بيج'])],
            'quantity' => ['required', 'integer', 'between:1,5'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'phone.regex' => 'أدخل رقم هاتف جزائري صحيح مثل 0555123456.',
            'required' => 'هذا الحقل مطلوب.',
            'customer_name.min' => 'الاسم الكامل يجب أن يحتوي على 3 أحرف على الأقل.',
            'address.min' => 'اكتب عنوانًا أكثر تفصيلًا.',
        ]);

        $communeExists = Commune::where('wilaya_id', $validated['wilaya_id'])
            ->where(function ($query) use ($validated) {
                $query->where('name', $validated['commune'])->orWhere('name_ar', $validated['commune']);
            })->exists();

        if (! $communeExists) {
            throw ValidationException::withMessages(['commune' => 'البلدية المختارة لا تتبع لهذه الولاية.']);
        }

        $order = DB::transaction(function () use ($validated) {
            $product = Product::where('slug', 'charm-tote')->lockForUpdate()->firstOrFail();
            if (! $product->active) {
                throw ValidationException::withMessages(['product' => 'المنتج غير متاح للطلب حاليًا.']);
            }

            $variant = $product->variants()->where('name', $validated['color'])->lockForUpdate()->first();
            if (! $variant || ! $variant->active) {
                throw ValidationException::withMessages(['color' => 'اللون المختار غير متوفر حاليًا.']);
            }
            if ($product->track_inventory && $variant->stock < $validated['quantity']) {
                throw ValidationException::withMessages(['quantity' => 'الكمية المطلوبة غير متوفرة في المخزون.']);
            }

            $wilaya = Wilaya::findOrFail($validated['wilaya_id']);
            $shipping = $this->shippingFees->forWilaya((int) $wilaya->id);
            $subtotal = $product->price * $validated['quantity'];

            $order = Order::create([
                'order_number' => $this->nextOrderNumber(),
                'status' => 'pending',
                'payment_status' => 'pending',
                'product_id' => $product->id,
                'product_variant_id' => $variant->id,
                'product_name' => $product->name,
                'color' => $variant->name,
                'quantity' => $validated['quantity'],
                'unit_price' => $product->price,
                'subtotal' => $subtotal,
                'shipping_fee' => $shipping['fee'],
                'total' => $subtotal + $shipping['fee'],
                'customer_name' => $validated['customer_name'],
                'phone' => preg_replace('/\s+/', '', $validated['phone']),
                'wilaya_id' => $wilaya->id,
                'wilaya_name' => $wilaya->name_ar,
                'commune' => $validated['commune'],
                'address' => $validated['address'],
                'notes' => $validated['notes'] ?? null,
                'delivery_type' => 'home',
                'payment_method' => 'cash_on_delivery',
                'inventory_tracked' => $product->track_inventory,
                'stock_restored' => ! $product->track_inventory,
            ]);

            if ($product->track_inventory) {
                $variant->decrement('stock', $validated['quantity']);
            }
            $order->history()->create(['action' => 'order_created', 'meta' => ['shipping_source' => $shipping['source']]]);

            return $order;
        });

        if (config('services.noest.auto_dispatch') && $this->noest->configured()) {
            try {
                $order = $this->noest->dispatch($order);
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return response()->json([
            'message' => 'تم استلام طلبك بنجاح. سنتصل بك للتأكيد.',
            'order' => $this->summary($order),
        ], 201);
    }

    private function nextOrderNumber(): string
    {
        do {
            $number = 'CH-'.now()->format('ymd').'-'.Str::upper(Str::random(5));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }

    private function summary(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'shipping_fee' => $order->shipping_fee,
            'total' => $order->total,
        ];
    }
}

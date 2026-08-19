<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json(['data' => $this->product()]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'price' => ['required', 'integer', 'between:100,1000000'],
            'compare_at_price' => ['nullable', 'integer', 'between:0,1000000'],
            'active' => ['required', 'boolean'],
            'track_inventory' => ['required', 'boolean'],
            'variants' => ['required', 'array', 'size:4'],
            'variants.*.key' => ['required', 'string', 'in:black,brown,green,beige'],
            'variants.*.stock' => ['required', 'integer', 'between:0,100000'],
            'variants.*.active' => ['required', 'boolean'],
        ]);

        DB::transaction(function () use ($validated) {
            $product = Product::where('slug', 'charm-tote')->firstOrFail();
            $product->update([
                'price' => $validated['price'],
                'compare_at_price' => $validated['compare_at_price'] ?? 0,
                'active' => $validated['active'],
                'track_inventory' => $validated['track_inventory'],
                'free_shipping' => false,
            ]);
            foreach ($validated['variants'] as $variant) {
                $product->variants()->where('key', $variant['key'])->update(['stock' => $variant['stock'], 'active' => $variant['active']]);
            }
        });

        return response()->json(['message' => 'تم تحديث السعر والمخزون.', 'data' => $this->product()]);
    }

    private function product(): Product
    {
        return Product::with('variants')->where('slug', 'charm-tote')->firstOrFail();
    }
}

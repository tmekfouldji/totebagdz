<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function index(): View
    {
        return view('store');
    }

    public function product(): JsonResponse
    {
        $product = Product::with('variants')->where('slug', 'charm-tote')->firstOrFail();

        return response()->json(['data' => $product]);
    }
}

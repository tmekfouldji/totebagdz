<?php

namespace App\Http\Controllers;

use App\Models\Commune;
use App\Models\Wilaya;
use App\Services\NoestService;
use App\Services\ShippingFeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function __construct(private NoestService $noest, private ShippingFeeService $fees) {}

    public function wilayas(): JsonResponse
    {
        if ($this->noest->configured()) {
            try {
                $rows = $this->noest->get('/api/public/get/wilayas');
                $arabic = Wilaya::pluck('name_ar', 'id');
                $data = collect($rows)->map(fn ($row) => [
                    'id' => (int) ($row['code'] ?? $row['id'] ?? 0),
                    'name' => $row['nom'] ?? $row['name'] ?? '',
                    'name_ar' => $arabic[(int) ($row['code'] ?? $row['id'] ?? 0)] ?? null,
                ])->filter(fn ($row) => $row['id'] && $row['name'])->values();
                if ($data->isNotEmpty()) {
                    return response()->json(['data' => $data, 'source' => 'noest']);
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return response()->json(['data' => Wilaya::orderBy('id')->get(), 'source' => 'database']);
    }

    public function communes(Request $request): JsonResponse
    {
        $validated = $request->validate(['wilaya_id' => ['required', 'integer', 'between:1,58']]);
        $wilayaId = (int) $validated['wilaya_id'];

        if ($this->noest->configured()) {
            try {
                $rows = $this->noest->get('/api/public/get/communes');
                $data = collect($rows)->filter(fn ($row) => (int) ($row['wilaya_id'] ?? 0) === $wilayaId && (int) ($row['is_active'] ?? 1) === 1)
                    ->values()->map(fn ($row, $index) => ['id' => $row['id'] ?? $index + 1, 'name' => $row['nom'] ?? $row['name']]);
                if ($data->isNotEmpty()) {
                    return response()->json(['data' => $data, 'source' => 'noest']);
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return response()->json(['data' => Commune::where('wilaya_id', $wilayaId)->orderBy('name_ar')->get(['id', 'name', 'name_ar']), 'source' => 'database']);
    }

    public function fees(Request $request): JsonResponse
    {
        $validated = $request->validate(['wilaya_id' => ['required', 'integer', 'between:1,58']]);
        $result = $this->fees->forWilaya((int) $validated['wilaya_id']);

        return response()->json(['data' => ['wilaya_id' => (int) $validated['wilaya_id'], 'home_fee' => $result['fee']], 'source' => $result['source']]);
    }
}

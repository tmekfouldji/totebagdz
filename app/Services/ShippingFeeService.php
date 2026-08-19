<?php

namespace App\Services;

class ShippingFeeService
{
    public function __construct(private NoestService $noest) {}

    public function forWilaya(int $wilayaId): array
    {
        try {
            $fee = $this->noest->deliveryFee($wilayaId);
            if ($fee) {
                return ['fee' => $fee, 'source' => 'noest'];
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return ['fee' => $this->fallback($wilayaId), 'source' => 'local-fallback'];
    }

    private function fallback(int $wilayaId): int
    {
        if (in_array($wilayaId, [9, 10, 15, 16, 26, 35, 42, 44], true)) {
            return 450;
        }
        if (in_array($wilayaId, [2, 5, 6, 18, 19, 25, 28, 34, 43, 48], true)) {
            return 500;
        }
        if (in_array($wilayaId, [4, 12, 13, 14, 20, 21, 22, 23, 24, 27, 29, 31, 36, 38, 40, 41, 46], true)) {
            return 600;
        }
        if (in_array($wilayaId, [3, 7, 17, 30, 32, 39, 45, 47, 51, 55, 57], true)) {
            return 850;
        }

        return 1100;
    }
}

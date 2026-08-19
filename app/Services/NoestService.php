<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class NoestService
{
    public function configured(): bool
    {
        return filled(config('services.noest.api_token')) && filled(config('services.noest.user_guid'));
    }

    public function get(string $path): array
    {
        if (! $this->configured()) {
            throw new RuntimeException('NOEST credentials are not configured.');
        }

        return $this->client()->get($path)->throw()->json();
    }

    public function deliveryFee(int $wilayaId): ?int
    {
        if (! $this->configured()) {
            return null;
        }

        $payload = $this->get('/api/public/fees');
        $rows = data_get($payload, 'tarifs.delivery', []);
        $match = collect($rows)->first(fn ($row) => (int) ($row['wilaya_id'] ?? 0) === $wilayaId);

        return $match ? (int) ($match['tarif'] ?? 0) : null;
    }

    public function dispatch(Order $order): Order
    {
        if ($order->noest_tracking) {
            throw new RuntimeException('This order has already been sent to NOEST.');
        }

        $payload = [
            'user_guid' => config('services.noest.user_guid'),
            'reference' => $order->order_number,
            'client' => $order->customer_name,
            'phone' => $order->phone,
            'adresse' => $order->address,
            'wilaya_id' => $order->wilaya_id,
            'commune' => $order->commune,
            'montant' => $order->total,
            'remarque' => $order->notes ?: "اللون: {$order->color}",
            'produit' => "Charm tote - {$order->color} × {$order->quantity}",
            'type_id' => 1,
            'poids' => 1,
            'stop_desk' => 0,
            'can_open' => 1,
        ];

        $response = $this->client()->post('/api/public/create/order', $payload);
        $body = $response->json();
        if (! $response->successful() || isset($body['error'])) {
            throw new RuntimeException((string) ($body['message'] ?? $body['error'] ?? 'NOEST rejected the order.'));
        }

        $tracking = (string) ($body['tracking'] ?? '');
        if ($tracking === '') {
            throw new RuntimeException('NOEST did not return a tracking number.');
        }

        if (config('services.noest.auto_validate', false)) {
            $this->client()->post('/api/public/valid/order', [
                'user_guid' => config('services.noest.user_guid'),
                'tracking' => $tracking,
            ])->throw();
        }

        $order->update([
            'status' => 'shipped',
            'noest_tracking' => $tracking,
            'noest_dispatched_at' => now(),
            'noest_response' => $body,
        ]);
        $order->history()->create(['action' => 'dispatched_to_noest', 'meta' => ['tracking' => $tracking]]);

        return $order->fresh(['history']);
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl(rtrim(config('services.noest.base_url'), '/'))
            ->withToken(config('services.noest.api_token'))
            ->acceptJson()
            ->asJson()
            ->timeout(10)
            ->retry(2, 350, throw: false);
    }
}

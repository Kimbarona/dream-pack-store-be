<?php

namespace App\Services\PaymentProviders;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class MockCryptoProvider implements PaymentProviderInterface
{
    private string $webhookSecret;
    private array $supportedCurrencies;
    private bool $simulateFailures;

    public function __construct()
    {
        $this->webhookSecret = config('services.mock_crypto.webhook_secret', 'mock-secret-key-for-testing');
        $this->supportedCurrencies = ['BTC', 'ETH', 'LTC', 'BCH'];
        $this->simulateFailures = config('services.mock_crypto.simulate_failures', false);
    }

    public function createInvoice(array $data): array
    {
        $orderId = $data['order_id'];
        $amount = $data['amount'];
        $currency = $data['currency'] ?? 'BTC';
        
        if (!in_array($currency, $this->getSupportedCurrencies())) {
            throw new \InvalidArgumentException("Currency {$currency} is not supported");
        }

        $providerRef = 'MOCK-' . strtoupper(uniqid());
        $address = $this->generateMockAddress($currency);
        $expiresAt = now()->addMinutes(30);
        
        $invoiceData = [
            'provider_reference' => $providerRef,
            'address' => $address,
            'amount' => $amount,
            'currency' => $currency,
            'status' => 'pending',
            'confirmations' => 0,
            'txid' => null,
            'received_amount' => 0,
            'expires_at' => $expiresAt->toISOString(),
            'created_at' => now()->toISOString(),
            'payment_url' => "https://mock-crypto-gateway.com/pay/{$providerRef}",
            'qr_code_url' => "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={$address}",
        ];

        Cache::put("crypto_invoice_{$providerRef}", $invoiceData, now()->addHour());

        Log::info('Mock crypto invoice created', [
            'order_id' => $orderId,
            'provider_reference' => $providerRef,
            'amount' => $amount,
            'currency' => $currency,
            'address' => $address,
        ]);

        return $invoiceData;
    }

    public function verifyInvoice(string $providerReference): array
    {
        $invoiceData = Cache::get("crypto_invoice_{$providerReference}");
        
        if (!$invoiceData) {
            return [
                'status' => 'not_found',
                'message' => 'Invoice not found or expired',
            ];
        }

        $currentConfirmations = $this->getSimulatedConfirmations($providerReference);
        $invoiceData['confirmations'] = $currentConfirmations;
        
        if ($currentConfirmations >= 6) {
            $invoiceData['status'] = 'confirmed';
            $invoiceData['txid'] = $this->generateMockTransactionId();
            $invoiceData['received_amount'] = $invoiceData['amount'];
        } elseif ($currentConfirmations > 0) {
            $invoiceData['status'] = 'partial';
            $invoiceData['txid'] = $this->generateMockTransactionId();
            $invoiceData['received_amount'] = $invoiceData['amount'];
        } else {
            $invoiceData['status'] = 'pending';
        }

        if (now()->parse($invoiceData['expires_at'])->isPast()) {
            $invoiceData['status'] = 'expired';
        }

        Cache::put("crypto_invoice_{$providerReference}", $invoiceData, now()->addHour());

        return [
            'status' => $invoiceData['status'],
            'confirmations' => $invoiceData['confirmations'],
            'txid' => $invoiceData['txid'],
            'received_amount' => $invoiceData['received_amount'],
            'amount_due' => $invoiceData['amount'] - $invoiceData['received_amount'],
            'is_expired' => now()->parse($invoiceData['expires_at'])->isPast(),
        ];
    }

    public function getProviderName(): string
    {
        return 'MockCrypto';
    }

    public function getSupportedCurrencies(): array
    {
        return $this->supportedCurrencies;
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function getWebhookSecret(): string
    {
        return $this->webhookSecret;
    }

    public function verifyWebhookSignature(array $payload, string $signature): bool
    {
        $expectedSignature = hash_hmac('sha256', json_encode($payload), $this->webhookSecret);
        return hash_equals($expectedSignature, $signature);
    }

    public function simulatePayment(string $providerReference, string $status = 'confirmed'): void
    {
        $invoiceData = Cache::get("crypto_invoice_{$providerReference}");
        
        if (!$invoiceData) {
            throw new \Exception("Invoice not found: {$providerReference}");
        }

        $confirmations = match($status) {
            'pending' => 0,
            'partial' => rand(1, 3),
            'confirmed' => rand(6, 10),
            'expired' => 0,
            default => 0,
        };

        $invoiceData['status'] = $status;
        $invoiceData['confirmations'] = $confirmations;
        
        if ($confirmations > 0) {
            $invoiceData['txid'] = $this->generateMockTransactionId();
            $invoiceData['received_amount'] = $invoiceData['amount'];
        }

        Cache::put("crypto_invoice_{$providerReference}", $invoiceData, now()->addHour());

        Log::info('Mock crypto payment simulated', [
            'provider_reference' => $providerReference,
            'status' => $status,
            'confirmations' => $confirmations,
        ]);
    }

    private function generateMockAddress(string $currency): string
    {
        $prefixes = [
            'BTC' => '1',
            'ETH' => '0x',
            'LTC' => 'L',
            'BCH' => 'bitcoincash:',
        ];

        $prefix = $prefixes[$currency] ?? '1';
        $length = $currency === 'ETH' ? 40 : 34;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        
        $address = $prefix;
        for ($i = 0; $i < $length - strlen($prefix); $i++) {
            $address .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $address;
    }

    private function generateMockTransactionId(): string
    {
        return 'tx_' . strtolower(Str::random(64));
    }

    private function getSimulatedConfirmations(string $providerReference): int
    {
        return Cache::remember("confirmations_{$providerReference}", 60, function () {
            if ($this->simulateFailures && rand(1, 10) === 1) {
                return 0;
            }
            
            $hoursSinceCreation = rand(1, 24);
            return min($hoursSinceCreation * 2, 10);
        });
    }

    public function getMockWebhookPayload(string $providerReference, string $event = 'payment.received'): array
    {
        $invoiceData = Cache::get("crypto_invoice_{$providerReference}");
        
        if (!$invoiceData) {
            throw new \Exception("Invoice not found: {$providerReference}");
        }

        $payload = [
            'event' => $event,
            'id' => 'evt_' . uniqid(),
            'created' => now()->timestamp,
            'data' => [
                'object' => [
                    'id' => $providerReference,
                    'status' => $invoiceData['status'],
                    'amount' => $invoiceData['amount'],
                    'currency' => $invoiceData['currency'],
                    'address' => $invoiceData['address'],
                    'confirmations' => $invoiceData['confirmations'],
                    'txid' => $invoiceData['txid'],
                    'received_amount' => $invoiceData['received_amount'],
                    'expires_at' => $invoiceData['expires_at'],
                ],
            ],
        ];

        $signature = hash_hmac('sha256', json_encode($payload), $this->getWebhookSecret());
        
        return [
            'payload' => $payload,
            'signature' => $signature,
        ];
    }

    public function getStats(): array
    {
        return [
            'provider' => $this->getProviderName(),
            'supported_currencies' => $this->getSupportedCurrencies(),
            'is_available' => $this->isAvailable(),
            'webhook_secret' => $this->getWebhookSecret(),
            'simulate_failures' => $this->simulateFailures,
        ];
    }
}
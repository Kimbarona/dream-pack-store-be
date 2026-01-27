<?php

namespace App\Services\PaymentProviders;

interface PaymentProviderInterface
{
    /**
     * Create a payment invoice for the given order
     */
    public function createInvoice(array $data): array;

    /**
     * Verify the status of an existing invoice
     */
    public function verifyInvoice(string $providerReference): array;

    /**
     * Get the provider name
     */
    public function getProviderName(): string;

    /**
     * Get supported crypto currencies
     */
    public function getSupportedCurrencies(): array;

    /**
     * Check if the provider is available
     */
    public function isAvailable(): bool;

    /**
     * Get webhook secret for signature verification
     */
    public function getWebhookSecret(): string;

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(array $payload, string $signature): bool;
}
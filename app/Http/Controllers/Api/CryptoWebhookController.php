<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CryptoInvoice;
use App\Models\Order;
use App\Services\PaymentProviders\MockCryptoProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CryptoWebhookController extends Controller
{
    private MockCryptoProvider $provider;

    public function __construct(MockCryptoProvider $provider)
    {
        $this->provider = $provider;
    }

    public function handle(Request $request): JsonResponse
    {
        try {
            $payload = $request->json()->all();
            $signature = $request->header('X-Signature');
            
            if (!$this->verifyWebhookSignature($payload, $signature)) {
                Log::warning('Invalid webhook signature received', [
                    'payload' => $payload,
                    'signature' => $signature,
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid signature',
                ], 401);
            }

            $eventId = $payload['id'] ?? null;
            $eventType = $payload['event'] ?? null;
            $invoiceData = $payload['data']['object'] ?? null;

            if (!$eventId || !$eventType || !$invoiceData) {
                Log::error('Invalid webhook payload structure', [
                    'payload' => $payload,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payload structure',
                ], 400);
            }

            $idempotencyKey = "webhook_{$eventId}";
            
            if (Cache::has($idempotencyKey)) {
                Log::info('Duplicate webhook event ignored', [
                    'event_id' => $eventId,
                    'event_type' => $eventType,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Duplicate event processed',
                ]);
            }

            Cache::put($idempotencyKey, true, now()->addHours(24));

            return DB::transaction(function () use ($payload, $eventType, $invoiceData, $eventId) {
                $providerRef = $invoiceData['id'] ?? null;
                
                if (!$providerRef) {
                    throw new \Exception('Provider reference missing in webhook payload');
                }

                $cryptoInvoice = CryptoInvoice::where('provider_ref', $providerRef)
                    ->lockForUpdate()
                    ->first();

                if (!$cryptoInvoice) {
                    Log::error('Crypto invoice not found for webhook', [
                        'provider_ref' => $providerRef,
                        'event_id' => $eventId,
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Invoice not found',
                    ], 404);
                }

                $previousStatus = $cryptoInvoice->status;
                
                $this->updateInvoiceFromWebhook($cryptoInvoice, $invoiceData, $eventType);
                
                $order = $cryptoInvoice->order;
                
                if ($cryptoInvoice->status === 'confirmed' && $previousStatus !== 'confirmed') {
                    $order->markAsPaidConfirmed();
                    
                    Log::info('Order marked as paid confirmed via webhook', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'invoice_id' => $cryptoInvoice->id,
                        'provider_ref' => $providerRef,
                        'txid' => $cryptoInvoice->txid,
                        'confirmations' => $cryptoInvoice->confirmations,
                    ]);
                } elseif ($cryptoInvoice->status === 'expired' && $previousStatus !== 'expired') {
                    $order->transitionStatus('pending_payment');
                    
                    Log::info('Order reset to pending payment due to expired invoice', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'invoice_id' => $cryptoInvoice->id,
                        'provider_ref' => $providerRef,
                    ]);
                }

                Log::info('Webhook processed successfully', [
                    'event_id' => $eventId,
                    'event_type' => $eventType,
                    'provider_ref' => $providerRef,
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'previous_status' => $previousStatus,
                    'new_status' => $cryptoInvoice->status,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Webhook processed successfully',
                    'data' => [
                        'event_id' => $eventId,
                        'event_type' => $eventType,
                        'order_id' => $order->id,
                        'invoice_status' => $cryptoInvoice->status,
                    ]
                ]);

            });

        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->json()->all(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed',
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    private function verifyWebhookSignature(array $payload, ?string $signature): bool
    {
        if (!$signature) {
            return false;
        }

        return $this->provider->verifyWebhookSignature($payload, $signature);
    }

    private function updateInvoiceFromWebhook(CryptoInvoice $invoice, array $invoiceData, string $eventType): void
    {
        $status = $invoiceData['status'] ?? $invoice->status;
        $confirmations = $invoiceData['confirmations'] ?? $invoice->confirmations;
        $txid = $invoiceData['txid'] ?? $invoice->txid;
        $receivedAmount = $invoiceData['received_amount'] ?? $invoice->received_amount;

        $invoice->update([
            'status' => $status,
            'confirmations' => $confirmations,
            'txid' => $txid,
            'received_amount' => $receivedAmount,
        ]);

        Log::info('Invoice updated from webhook', [
            'invoice_id' => $invoice->id,
            'provider_ref' => $invoice->provider_ref,
            'event_type' => $eventType,
            'previous_status' => $invoice->getOriginal('status'),
            'new_status' => $status,
            'confirmations' => $confirmations,
            'txid' => $txid,
            'received_amount' => $receivedAmount,
        ]);
    }

    public function testWebhook(): JsonResponse
    {
        if (!config('app.debug')) {
            return response()->json([
                'success' => false,
                'message' => 'Test endpoint only available in debug mode',
            ], 403);
        }

        try {
            $testInvoice = CryptoInvoice::first();
            
            if (!$testInvoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'No crypto invoices found to test with',
                ]);
            }

            $webhookData = $this->provider->getMockWebhookPayload(
                $testInvoice->provider_ref,
                'payment.confirmed'
            );

            return response()->json([
                'success' => true,
                'message' => 'Test webhook payload generated',
                'data' => [
                    'payload' => $webhookData['payload'],
                    'signature' => $webhookData['signature'],
                    'webhook_url' => route('api.webhooks.crypto'),
                    'curl_command' => $this->generateTestCurlCommand($webhookData),
                    'invoice_info' => [
                        'id' => $testInvoice->id,
                        'provider_ref' => $testInvoice->provider_ref,
                        'current_status' => $testInvoice->status,
                        'order_id' => $testInvoice->order_id,
                    ],
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Test webhook generation failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate test webhook',
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    private function generateTestCurlCommand(array $webhookData): string
    {
        $payload = json_encode($webhookData['payload']);
        $signature = $webhookData['signature'];
        $url = route('api.webhooks.crypto');

        return "curl -X POST \"{$url}\" \\\n" .
               "  -H \"Content-Type: application/json\" \\\n" .
               "  -H \"X-Signature: {$signature}\" \\\n" .
               "  -d '{$payload}'";
    }

    public function getWebhookInfo(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'webhook_url' => route('api.webhooks.crypto'),
                'signature_header' => 'X-Signature',
                'signature_algorithm' => 'HMAC-SHA256',
                'supported_events' => [
                    'payment.pending',
                    'payment.partial',
                    'payment.confirmed',
                    'payment.expired',
                    'payment.failed',
                ],
                'idempotency' => [
                    'key_source' => 'payload.id',
                    'cache_duration' => '24 hours',
                ],
                'provider_info' => $this->provider->getStats(),
            ]
        ]);
    }
}
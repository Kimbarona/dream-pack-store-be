<?php

namespace App\Jobs;

use App\Models\CryptoInvoice;
use App\Services\PaymentProviders\MockCryptoProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CheckCryptoInvoiceStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;
    public int $backoff = [30, 60, 120];

    private CryptoInvoice $invoice;
    private MockCryptoProvider $provider;

    public function __construct(CryptoInvoice $invoice, MockCryptoProvider $provider)
    {
        $this->invoice = $invoice;
        $this->provider = $provider;
    }

    public function handle(): void
    {
        try {
            if ($this->invoice->status === 'confirmed' || $this->invoice->status === 'expired') {
                Log::info('Skipping status check for completed invoice', [
                    'invoice_id' => $this->invoice->id,
                    'status' => $this->invoice->status,
                ]);
                return;
            }

            $verificationResult = $this->provider->verifyInvoice($this->invoice->provider_ref);

            $previousStatus = $this->invoice->status;

            DB::transaction(function () use ($verificationResult, $previousStatus) {
                $this->invoice->update([
                    'status' => $verificationResult['status'],
                    'confirmations' => $verificationResult['confirmations'],
                    'txid' => $verificationResult['txid'] ?? $this->invoice->txid,
                    'received_amount' => $verificationResult['received_amount'] ?? $this->invoice->received_amount,
                ]);

                if ($verificationResult['status'] === 'confirmed' && $previousStatus !== 'confirmed') {
                    $order = $this->invoice->order;
                    $order->markAsPaidConfirmed();

                    Log::info('Order automatically confirmed via status check', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'invoice_id' => $this->invoice->id,
                        'provider_ref' => $this->invoice->provider_ref,
                        'txid' => $this->invoice->txid,
                        'confirmations' => $this->invoice->confirmations,
                    ]);
                } elseif ($verificationResult['status'] === 'expired' && $previousStatus !== 'expired') {
                    $order = $this->invoice->order;
                    $order->transitionStatus('pending_payment');

                    Log::info('Order reset due to expired invoice', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'invoice_id' => $this->invoice->id,
                        'provider_ref' => $this->invoice->provider_ref,
                    ]);
                }

                Log::info('Crypto invoice status updated', [
                    'invoice_id' => $this->invoice->id,
                    'provider_ref' => $this->invoice->provider_ref,
                    'order_id' => $this->invoice->order_id,
                    'previous_status' => $previousStatus,
                    'new_status' => $verificationResult['status'],
                    'confirmations' => $verificationResult['confirmations'],
                    'txid' => $verificationResult['txid'],
                    'received_amount' => $verificationResult['received_amount'],
                    'amount_due' => $verificationResult['amount_due'] ?? null,
                    'is_expired' => $verificationResult['is_expired'] ?? false,
                ]);
            });

        } catch (\Exception $e) {
            Log::error('Crypto invoice status check failed', [
                'invoice_id' => $this->invoice->id,
                'provider_ref' => $this->invoice->provider_ref,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->fail($e);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('CryptoInvoiceStatusJob failed permanently', [
            'invoice_id' => $this->invoice->id,
            'provider_ref' => $this->invoice->provider_ref,
            'attempts' => $this->attempts(),
            'error' => $exception->getMessage(),
        ]);

        if ($this->attempts() >= $this->tries) {
            Log::alert('Crypto invoice status monitoring failed after max retries', [
                'invoice_id' => $this->invoice->id,
                'order_id' => $this->invoice->order_id,
                'provider_ref' => $this->invoice->provider_ref,
                'manual_intervention_required' => true,
            ]);
        }
    }

    public function displayName(): string
    {
        return "Check Crypto Invoice Status: {$this->invoice->provider_ref}";
    }

    public function tags(): array
    {
        return [
            'crypto-status-check',
            "order:{$this->invoice->order_id}",
            "invoice:{$this->invoice->id}",
            "provider:{$this->provider->getProviderName()}",
        ];
    }

    public static function dispatchForPendingInvoices(): void
    {
        $pendingInvoices = CryptoInvoice::whereIn('status', ['pending', 'partial'])
            ->where('expires_at', '>', now())
            ->with(['order'])
            ->get();

        $provider = app(MockCryptoProvider::class);

        foreach ($pendingInvoices as $invoice) {
            try {
                dispatch(new self($invoice, $provider))
                    ->onQueue('crypto-monitoring')
                    ->delay(now()->addSeconds(rand(1, 30)));
            } catch (\Exception $e) {
                Log::error('Failed to dispatch status check job', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Dispatched status check jobs for pending invoices', [
            'count' => $pendingInvoices->count(),
            'queue' => 'crypto-monitoring',
        ]);
    }

    public static function dispatchForExpiredInvoices(): void
    {
        $expiredInvoices = CryptoInvoice::where('status', '!=', 'expired')
            ->where('status', '!=', 'confirmed')
            ->where('expires_at', '<', now()->subMinutes(5))
            ->with(['order'])
            ->get();

        $provider = app(MockCryptoProvider::class);

        foreach ($expiredInvoices as $invoice) {
            try {
                dispatch(new self($invoice, $provider))
                    ->onQueue('crypto-monitoring')
                    ->delay(now()->addSeconds(rand(1, 15)));
            } catch (\Exception $e) {
                Log::error('Failed to dispatch expired invoice check job', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Dispatched status check jobs for expired invoices', [
            'count' => $expiredInvoices->count(),
            'queue' => 'crypto-monitoring',
        ]);
    }
}
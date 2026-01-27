<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateCryptoInvoiceRequest;
use App\Models\Order;
use App\Models\CryptoInvoice;
use App\Services\PaymentProviders\MockCryptoProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CryptoPaymentController extends Controller
{
    private MockCryptoProvider $provider;

    public function __construct(MockCryptoProvider $provider)
    {
        $this->provider = $provider;
    }

    public function createInvoice(CreateCryptoInvoiceRequest $request, Order $order): JsonResponse
    {
        try {
            if ($order->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            if ($order->is_paid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order is already paid',
                ], 400);
            }

            $existingInvoice = CryptoInvoice::where('order_id', $order->id)
                ->where('status', '!=', 'expired')
                ->where('expires_at', '>', now())
                ->first();

            if ($existingInvoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Active invoice already exists for this order',
                    'data' => [
                        'invoice_id' => $existingInvoice->id,
                        'provider_reference' => $existingInvoice->provider_ref,
                        'amount' => $existingInvoice->amount,
                        'crypto_type' => $existingInvoice->crypto_type,
                        'address' => $existingInvoice->address,
                        'status' => $existingInvoice->status,
                        'expires_at' => $existingInvoice->expires_at,
                    ],
                ], 400);
            }

            return DB::transaction(function () use ($request, $order) {
                $invoiceData = $this->provider->createInvoice([
                    'order_id' => $order->id,
                    'amount' => $order->total,
                    'currency' => $request->crypto_type,
                ]);

                $cryptoInvoice = CryptoInvoice::create([
                    'order_id' => $order->id,
                    'provider_ref' => $invoiceData['provider_reference'],
                    'crypto_type' => $invoiceData['currency'],
                    'amount' => $invoiceData['amount'],
                    'address' => $invoiceData['address'],
                    'status' => $invoiceData['status'],
                    'confirmations' => $invoiceData['confirmations'],
                    'txid' => $invoiceData['txid'],
                    'received_amount' => $invoiceData['received_amount'],
                    'expires_at' => $invoiceData['expires_at'],
                    'payment_url' => $invoiceData['payment_url'],
                    'qr_code_url' => $invoiceData['qr_code_url'],
                ]);

                $order->markAsPaidUnconfirmed();

                Log::info('Crypto invoice created', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'invoice_id' => $cryptoInvoice->id,
                    'provider_ref' => $cryptoInvoice->provider_ref,
                    'crypto_type' => $cryptoInvoice->crypto_type,
                    'amount' => $cryptoInvoice->amount,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Crypto invoice created successfully',
                    'data' => [
                        'order' => [
                            'id' => $order->id,
                            'order_number' => $order->order_number,
                            'total' => $order->total,
                            'status' => $order->status,
                            'status_label' => $order->status_label,
                        ],
                        'invoice' => [
                            'id' => $cryptoInvoice->id,
                            'provider_reference' => $cryptoInvoice->provider_ref,
                            'crypto_type' => $cryptoInvoice->crypto_type,
                            'amount' => $cryptoInvoice->amount,
                            'address' => $cryptoInvoice->address,
                            'status' => $cryptoInvoice->status,
                            'confirmations' => $cryptoInvoice->confirmations,
                            'txid' => $cryptoInvoice->txid,
                            'received_amount' => $cryptoInvoice->received_amount,
                            'amount_due' => $cryptoInvoice->amount - $cryptoInvoice->received_amount,
                            'expires_at' => $cryptoInvoice->expires_at,
                            'payment_url' => $cryptoInvoice->payment_url,
                            'qr_code_url' => $cryptoInvoice->qr_code_url,
                            'created_at' => $cryptoInvoice->created_at,
                        ],
                        'instructions' => $this->getPaymentInstructions($cryptoInvoice),
                        'time_remaining' => $this->getTimeRemaining($cryptoInvoice->expires_at),
                    ]
                ], 201);
            });

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);

        } catch (\Exception $e) {
            Log::error('Crypto invoice creation failed', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create crypto invoice. Please try again.',
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function getInvoiceStatus(Order $order, CryptoInvoice $invoice): JsonResponse
    {
        try {
            if ($invoice->order_id !== $order->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice not found for this order',
                ], 404);
            }

            if ($order->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            $verificationResult = $this->provider->verifyInvoice($invoice->provider_ref);

            if ($verificationResult['status'] !== $invoice->status) {
                $invoice->status = $verificationResult['status'];
                $invoice->confirmations = $verificationResult['confirmations'];
                $invoice->txid = $verificationResult['txid'];
                $invoice->received_amount = $verificationResult['received_amount'] ?? $invoice->received_amount;
                $invoice->save();

                if ($verificationResult['status'] === 'confirmed') {
                    $order->markAsPaidConfirmed();
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'invoice' => [
                        'id' => $invoice->id,
                        'provider_reference' => $invoice->provider_ref,
                        'crypto_type' => $invoice->crypto_type,
                        'amount' => $invoice->amount,
                        'address' => $invoice->address,
                        'status' => $verificationResult['status'],
                        'confirmations' => $verificationResult['confirmations'],
                        'txid' => $verificationResult['txid'],
                        'received_amount' => $verificationResult['received_amount'],
                        'amount_due' => $verificationResult['amount_due'] ?? ($invoice->amount - $verificationResult['received_amount']),
                        'is_expired' => $verificationResult['is_expired'] ?? false,
                        'expires_at' => $invoice->expires_at,
                        'created_at' => $invoice->created_at,
                        'updated_at' => $invoice->updated_at,
                    ],
                    'order' => [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'status_label' => $order->status_label,
                        'is_paid' => $order->is_paid(),
                    ],
                    'time_remaining' => $this->getTimeRemaining($invoice->expires_at),
                    'next_actions' => $this->getNextActions($verificationResult['status'], $invoice),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Crypto invoice status check failed', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'invoice_id' => $invoice->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to check invoice status',
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function getSupportedCurrencies(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'currencies' => $this->provider->getSupportedCurrencies(),
                'provider' => [
                    'name' => $this->provider->getProviderName(),
                    'is_available' => $this->provider->isAvailable(),
                ],
                'limits' => [
                    'min_amount' => 0.0001,
                    'max_amount' => 10000,
                    'expiry_minutes' => 30,
                    'confirmations_required' => 6,
                ],
            ]
        ]);
    }

    private function getPaymentInstructions(CryptoInvoice $invoice): array
    {
        return [
            'send_exact_amount' => "Send exactly {$invoice->amount} {$invoice->crypto_type} to the address below",
            'address' => $invoice->address,
            'qr_code_instruction' => 'Scan the QR code with your crypto wallet',
            'confirmation_time' => 'Payment will be confirmed after 6 network confirmations (approximately 30-60 minutes)',
            'expiry_warning' => 'Invoice expires in 30 minutes. Payment must be made before expiry.',
            'overpayment_warning' => 'Overpayments may not be automatically credited. Please send the exact amount.',
        ];
    }

    private function getTimeRemaining(string $expiresAt): array
    {
        $expiry = \Carbon\Carbon::parse($expiresAt);
        $now = now();
        
        if ($expiry->isPast()) {
            return [
                'expired' => true,
                'minutes' => 0,
                'seconds' => 0,
                'human_readable' => 'Expired',
            ];
        }

        $diff = $expiry->diff($now);
        
        return [
            'expired' => false,
            'minutes' => $diff->i + ($diff->h * 60) + ($diff->d * 24 * 60),
            'seconds' => $diff->s,
            'human_readable' => $diff->format('%i minutes %s seconds'),
        ];
    }

    private function getNextActions(string $status, CryptoInvoice $invoice): array
    {
        switch ($status) {
            case 'pending':
                return [
                    'type' => 'payment_required',
                    'message' => 'Please send the required amount to complete your payment.',
                    'actions' => [
                        'Send crypto to the provided address',
                        'Wait for network confirmations',
                    ],
                ];

            case 'partial':
                return [
                    'type' => 'confirmation_pending',
                    'message' => 'Payment received and waiting for confirmations.',
                    'actions' => [
                        'Wait for remaining confirmations',
                        'Monitor your order status',
                    ],
                ];

            case 'confirmed':
                return [
                    'type' => 'payment_complete',
                    'message' => 'Payment confirmed! Your order will be processed soon.',
                    'actions' => [
                        'Track your order status',
                        'Wait for shipping confirmation',
                    ],
                ];

            case 'expired':
                return [
                    'type' => 'invoice_expired',
                    'message' => 'Invoice has expired. Please create a new invoice.',
                    'actions' => [
                        'Create a new crypto invoice',
                        'Contact support if needed',
                    ],
                ];

            default:
                return [
                    'type' => 'unknown_status',
                    'message' => 'Please contact support for assistance.',
                ];
        }
    }
}
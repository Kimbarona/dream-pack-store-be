<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TraditionalPaymentController extends Controller
{
    public function createPayment(Request $request, Order $order): JsonResponse
    {
        try {
            if ($order->user_id !== \Illuminate\Support\Facades\Auth::id()) {
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

            $existingPayment = Payment::where('order_id', $order->id)
                ->where('status', '!=', 'failed')
                ->first();

            if ($existingPayment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Active payment already exists for this order',
                    'data' => [
                        'payment_id' => $existingPayment->id,
                        'payment_method' => $existingPayment->payment_method,
                        'amount' => $existingPayment->amount,
                        'status' => $existingPayment->status,
                        'transaction_id' => $existingPayment->transaction_id,
                    ],
                ], 400);
            }

            $paymentMethod = $request->input('payment_method', 'credit_card');
            $allowedMethods = ['credit_card', 'debit_card', 'bank_transfer', 'paypal'];
            
            if (!in_array($paymentMethod, $allowedMethods)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payment method',
                    'allowed_methods' => $allowedMethods,
                ], 400);
            }

            return DB::transaction(function () use ($order, $paymentMethod) {
                $transactionId = 'txn_' . strtolower(Str::random(32));
                $paymentUrl = $this->generateMockPaymentUrl($transactionId, $paymentMethod);

                $payment = Payment::create([
                    'order_id' => $order->id,
                    'payment_method' => $paymentMethod,
                    'amount' => $order->total,
                    'status' => 'pending',
                    'transaction_id' => $transactionId,
                    'gateway_response' => [
                        'payment_url' => $paymentUrl,
                        'expires_at' => now()->addMinutes(15)->toISOString(),
                        'mock_gateway' => true,
                    ],
                ]);

                $order->markAsPaidUnconfirmed();

                Log::info('Traditional payment created', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'payment_id' => $payment->id,
                    'payment_method' => $paymentMethod,
                    'amount' => $payment->amount,
                    'transaction_id' => $transactionId,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment created successfully',
                    'data' => [
                        'order' => [
                            'id' => $order->id,
                            'order_number' => $order->order_number,
                            'total' => $order->total,
                            'status' => $order->status,
                            'status_label' => $order->status_label,
                        ],
                        'payment' => [
                            'id' => $payment->id,
                            'payment_method' => $payment->payment_method,
                            'amount' => $payment->amount,
                            'status' => $payment->status,
                            'transaction_id' => $payment->transaction_id,
                            'payment_url' => $paymentUrl,
                            'expires_at' => now()->addMinutes(15)->toISOString(),
                            'created_at' => $payment->created_at,
                        ],
                        'instructions' => $this->getPaymentInstructions($paymentMethod, $payment),
                        'time_remaining' => $this->getTimeRemaining(now()->addMinutes(15)),
                    ]
                ], 201);
            });

        } catch (\Exception $e) {
            Log::error('Traditional payment creation failed', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'user_id' => \Illuminate\Support\Facades\Auth::id(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment. Please try again.',
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function getPaymentStatus(Order $order, Payment $payment): JsonResponse
    {
        try {
            if ($payment->order_id !== $order->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found for this order',
                ], 404);
            }

            if ($order->user_id !== \Illuminate\Support\Facades\Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            $this->updatePaymentStatus($payment);

            return response()->json([
                'success' => true,
                'data' => [
                    'payment' => [
                        'id' => $payment->id,
                        'payment_method' => $payment->payment_method,
                        'amount' => $payment->amount,
                        'status' => $payment->status,
                        'transaction_id' => $payment->transaction_id,
                        'gateway_response' => $payment->gateway_response,
                        'created_at' => $payment->created_at,
                        'updated_at' => $payment->updated_at,
                    ],
                    'order' => [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'status_label' => $order->status_label,
                        'is_paid' => $order->is_paid(),
                    ],
                    'next_actions' => $this->getNextActions($payment->status, $payment),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Payment status check failed', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'payment_id' => $payment->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to check payment status',
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function simulatePaymentSuccess(Payment $payment): JsonResponse
    {
        if (!config('app.debug')) {
            return response()->json([
                'success' => false,
                'message' => 'Payment simulation only available in debug mode',
            ], 403);
        }

        try {
            $payment->update([
                'status' => 'completed',
                'gateway_response' => array_merge($payment->gateway_response ?? [], [
                    'simulated_success' => true,
                    'completed_at' => now()->toISOString(),
                ]),
            ]);

            $order = $payment->order;
            $order->markAsPaidConfirmed();

            Log::info('Payment success simulated', [
                'payment_id' => $payment->id,
                'order_id' => $order->id,
                'amount' => $payment->amount,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment success simulated',
                'data' => [
                    'payment_id' => $payment->id,
                    'order_id' => $order->id,
                    'order_status' => $order->status,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Payment simulation failed', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to simulate payment',
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    private function generateMockPaymentUrl(string $transactionId, string $paymentMethod): string
    {
        $baseUrl = config('app.url');
        return "{$baseUrl}/payment/mock/{$transactionId}?method={$paymentMethod}";
    }

    private function getPaymentInstructions(string $paymentMethod, Payment $payment): array
    {
        switch ($paymentMethod) {
            case 'credit_card':
            case 'debit_card':
                return [
                    'redirect_to_gateway' => 'You will be redirected to a secure payment page',
                    'card_details_required' => 'Enter your card details on the payment page',
                    'verification_required' => 'Complete 3D secure verification if prompted',
                    'redirect_url' => $payment->gateway_response['payment_url'] ?? null,
                ];

            case 'bank_transfer':
                return [
                    'transfer_instructions' => 'Transfer the exact amount to the provided bank account',
                    'reference_required' => 'Use the transaction ID as payment reference',
                    'processing_time' => 'Bank transfers may take 1-2 business days to process',
                ];

            case 'paypal':
                return [
                    'paypal_redirect' => 'You will be redirected to PayPal for payment',
                    'login_required' => 'Login to your PayPal account to complete payment',
                    'instant_confirmation' => 'Payment will be confirmed instantly upon completion',
                ];

            default:
                return [
                    'contact_support' => 'Please contact customer support for payment assistance',
                ];
        }
    }

    private function getTimeRemaining($expiresAt): array
    {
        $now = now();
        
        if ($expiresAt->isPast()) {
            return [
                'expired' => true,
                'minutes' => 0,
                'seconds' => 0,
                'human_readable' => 'Expired',
            ];
        }

        $diff = $expiresAt->diff($now);
        
        return [
            'expired' => false,
            'minutes' => $diff->i + ($diff->h * 60),
            'seconds' => $diff->s,
            'human_readable' => $diff->format('%i minutes %s seconds'),
        ];
    }

    private function updatePaymentStatus(Payment $payment): void
    {
        if ($payment->status === 'completed') {
            return;
        }

        $createdAt = $payment->created_at;
        $expiresAt = $createdAt->addMinutes(15);

        if (now()->isAfter($expiresAt) && $payment->status === 'pending') {
            $payment->update([
                'status' => 'expired',
                'gateway_response' => array_merge($payment->gateway_response ?? [], [
                    'expired_at' => now()->toISOString(),
                ]),
            ]);

            $order = $payment->order;
            $order->transitionStatus('pending_payment');
        }
    }

    private function getNextActions(string $status, Payment $payment): array
    {
        switch ($status) {
            case 'pending':
                return [
                    'type' => 'payment_required',
                    'message' => 'Please complete the payment process.',
                    'actions' => [
                        'Complete payment using the provided payment URL',
                        'Ensure payment is completed before expiry',
                    ],
                ];

            case 'completed':
                return [
                    'type' => 'payment_complete',
                    'message' => 'Payment completed successfully! Your order will be processed.',
                    'actions' => [
                        'Track your order status',
                        'Wait for shipping confirmation',
                    ],
                ];

            case 'expired':
                return [
                    'type' => 'payment_expired',
                    'message' => 'Payment has expired. Please create a new payment.',
                    'actions' => [
                        'Create a new payment request',
                        'Contact support if needed',
                    ],
                ];

            case 'failed':
                return [
                    'type' => 'payment_failed',
                    'message' => 'Payment failed. Please try again.',
                    'actions' => [
                        'Create a new payment request',
                        'Check your payment details',
                        'Contact support if issue persists',
                    ],
                ];

            default:
                return [
                    'type' => 'unknown_status',
                    'message' => 'Please contact support for assistance.',
                ];
        }
    }

    public function getSupportedMethods(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'methods' => [
                    [
                        'code' => 'credit_card',
                        'name' => 'Credit Card',
                        'description' => 'Pay with Visa, Mastercard, or American Express',
                        'icon' => 'credit-card',
                        'fees' => '0% processing fee',
                    ],
                    [
                        'code' => 'debit_card',
                        'name' => 'Debit Card',
                        'description' => 'Pay with your debit card',
                        'icon' => 'credit-card',
                        'fees' => '0% processing fee',
                    ],
                    [
                        'code' => 'bank_transfer',
                        'name' => 'Bank Transfer',
                        'description' => 'Direct bank transfer from your account',
                        'icon' => 'university',
                        'fees' => '0% processing fee',
                        'processing_time' => '1-2 business days',
                    ],
                    [
                        'code' => 'paypal',
                        'name' => 'PayPal',
                        'description' => 'Pay with your PayPal account',
                        'icon' => 'paypal',
                        'fees' => '2.9% + $0.30 processing fee',
                    ],
                ],
                'limits' => [
                    'min_amount' => 1.00,
                    'max_amount' => 10000.00,
                    'currency' => 'ILS',
                    'expiry_minutes' => 15,
                ],
            ]
        ]);
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreOrderRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Admin can view all orders, regular users see only their own
            if ($user->role === 'admin' && $request->has('user_id')) {
                $orders = Order::with(['items', 'user'])
                    ->where('user_id', $request->user_id)
                    ->orderBy('created_at', 'desc')
                    ->paginate($request->get('per_page', 15));
            } else {
                $orders = Order::with(['items', 'user'])
                    ->where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->paginate($request->get('per_page', 15));
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'orders' => collect($orders->items)->map(function ($order) {
                        return [
                            'id' => $order->id,
                            'order_number' => $order->order_number,
                            'status' => $order->status,
                            'status_label' => $order->status_label,
                            'subtotal' => $order->subtotal,
                            'tax_amount' => $order->tax_amount,
                            'shipping_amount' => $order->shipping_amount,
                            'total' => $order->total,
                            'total_items' => $order->total_items,
                            'is_paid' => $order->is_paid(),
                            'created_at' => $order->created_at,
                        ];
                    }),
                    'pagination' => [
                        'current_page' => $orders->currentPage(),
                        'per_page' => $orders->perPage(),
                        'total' => $orders->total(),
                        'last_page' => $orders->lastPage(),
                    ],
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to fetch orders', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch orders.',
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $request->validateInventory();

            return DB::transaction(function () use ($request) {
                $orderData = $request->getOrderData();
                
                $order = Order::create([
                    'user_id' => $orderData['user_id'],
                    'shipping_address' => $orderData['shipping_address'],
                    'billing_address' => $orderData['billing_address'],
                    'notes' => $orderData['notes'],
                    'tax_amount' => 0, // TODO: Calculate tax based on location
                    'shipping_amount' => 0, // TODO: Calculate shipping based on weight/distance
                    'subtotal' => 0,
                    'total' => 0,
                ]);

                $subtotal = 0;
                
                foreach ($orderData['items'] as $itemData) {
                    $product = Product::lockForUpdate()->find($itemData['product_id']);
                    
                    if ($product->track_inventory) {
                        $newStock = $product->stock_qty - $itemData['quantity'];
                        
                        if ($newStock < 0) {
                            throw new \Exception("Insufficient stock for product: {$product->title}");
                        }
                        
                        $product->stock_qty = $newStock;
                        $product->save();
                    }

                    $chosenColor = null;
                    $colorInput = $itemData['color'] ?? null;
                    if (!empty($colorInput)) {
                        $chosenColor = [
                            'name' => $colorInput,
                            'value' => $colorInput,
                            'slug' => strtolower($colorInput),
                        ];
                    } elseif (!empty($itemData['chosen_color'])) {
                        $chosenColor = $itemData['chosen_color'];
                    }

                    $size = $itemData['size'] ?? $product->size;
                    $piecesPerPackage = $itemData['pieces_per_package'] ?? $product->pieces_per_package;

                    $unitPrice = isset($itemData['price']) ? (float) $itemData['price'] : $product->price;
                    $salePrice = $product->sale_price;
                    $effectivePrice = $salePrice && $salePrice < $unitPrice ? $salePrice : $unitPrice;
                    $totalPrice = $effectivePrice * $itemData['quantity'];

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_title' => $product->title,
                        'product_sku' => $product->sku,
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $unitPrice,
                        'sale_price' => $salePrice,
                        'total_price' => $totalPrice,
                        'size' => $size,
                        'chosen_color' => $chosenColor,
                        'pieces_per_package' => $piecesPerPackage,
                    ]);

                    $subtotal += $totalPrice;
                }

                $order->subtotal = $subtotal;
                $order->total = $subtotal + $order->tax_amount + $order->shipping_amount;
                $order->save();

                $order->load(['items', 'user']);

                Log::info('Order created successfully', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'user_id' => $order->user_id,
                    'total_items' => $order->total_items,
                    'total_amount' => $order->total,
                    'payment_method' => $orderData['payment_method'],
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Order created successfully',
                    'data' => [
                        'order' => [
                            'id' => $order->id,
                            'order_number' => $order->order_number,
                            'status' => $order->status,
                            'status_label' => $order->status_label,
                            'subtotal' => $order->subtotal,
                            'tax_amount' => $order->tax_amount,
                            'shipping_amount' => $order->shipping_amount,
                            'total' => $order->total,
                            'total_items' => $order->total_items,
                            'created_at' => $order->created_at,
                        ],
                        'items' => $order->items->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'product_title' => $item->product_title,
                                'product_sku' => $item->product_sku,
                                'quantity' => $item->quantity,
                                'unit_price' => $item->unit_price,
                                'sale_price' => $item->sale_price,
                                'total_price' => $item->total_price,
                                'size' => $item->size,
                                'chosen_color' => $item->chosen_color,
                                'pieces_per_package' => $item->pieces_per_package,
                                'effective_price' => $item->effective_price,
                                'formatted_unit_price' => $item->formatted_unit_price,
                                'formatted_sale_price' => $item->formatted_sale_price,
                                'formatted_total_price' => $item->formatted_total_price,
                                'is_on_sale' => $item->is_on_sale,
                                'discount_percentage' => $item->discount_percentage,
                            ];
                        }),
                        'payment_method' => $orderData['payment_method'],
                        'next_steps' => $this->getNextSteps($orderData['payment_method'], $order),
                    ]
                ], 201);

            });

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Order creation failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create order. Please try again.',
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    protected function getNextSteps(string $paymentMethod, Order $order): array
    {
        switch ($paymentMethod) {
            case 'crypto':
                return [
                    'type' => 'crypto_payment',
                    'message' => 'Please complete payment using cryptocurrency.',
                    'action_url' => route('api.payments.crypto.create', ['order' => $order->id]),
                ];
                
            case 'traditional':
                return [
                    'type' => 'traditional_payment',
                    'message' => 'Redirecting to payment gateway...',
                    'action_url' => route('api.payments.traditional.create', ['order' => $order->id]),
                ];
                
            default:
                return [
                    'type' => 'contact_support',
                    'message' => 'Please contact customer support for payment options.',
                ];
        }
    }

    public function show(Order $order): JsonResponse
    {
        if ($order->user_id !== Auth::id() && !(Auth::check() && Auth::user()->role === 'admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        $order->load([
            'items',
            'payments' => function ($query) {
                $query->latest();
            },
            'cryptoInvoices' => function ($query) {
                $query->latest();
            },
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'status_label' => $order->status_label,
                    'subtotal' => $order->subtotal,
                    'tax_amount' => $order->tax_amount,
                    'shipping_amount' => $order->shipping_amount,
                    'total' => $order->total,
                    'total_items' => $order->total_items,
                    'shipping_address' => $order->shipping_address,
                    'billing_address' => $order->billing_address,
                    'notes' => $order->notes,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                    'is_paid' => $order->isPaid(),
                    'is_pending_payment' => $order->isPendingPayment(),
                    'is_paid_unconfirmed' => $order->isPaidUnconfirmed(),
                    'can_be_cancelled' => $order->canBeCancelled(),
                    'can_be_paid' => $order->canBePaid,
                    'is_completed' => $order->is_completed,
                    'formatted_total' => $order->formatted_total,
                ],
                'items' => $order->items->map(function ($item) {
                    return array_merge($item->toArray(), [
                        'snapshot_data' => $item->getSnapshotData(),
                    ]);
                }),
                'payments' => $order->payments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => $payment->amount,
                        'status' => $payment->status,
                        'payment_method' => $payment->payment_method,
                        'transaction_id' => $payment->transaction_id,
                        'created_at' => $payment->created_at,
                        'updated_at' => $payment->updated_at,
                    ];
                }),
                'crypto_invoices' => $order->cryptoInvoices->map(function ($invoice) {
                    return [
                        'id' => $invoice->id,
                        'crypto_type' => $invoice->crypto_type,
                        'amount' => $invoice->amount,
                        'address' => $invoice->address,
                        'status' => $invoice->status,
                        'confirmations' => $invoice->confirmations,
                        'txid' => $invoice->txid,
                        'expires_at' => $invoice->expires_at,
                        'created_at' => $invoice->created_at,
                        'updated_at' => $invoice->updated_at,
                    ];
                }),
            ]
        ]);
    }

    public function getOrdersByUserId(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|string',
        ]);

        $orders = Order::with(['items.product', 'user'])
            ->where('user_id', $request->user_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'orders' => $orders->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'status_label' => $order->status_label,
                        'subtotal' => $order->subtotal,
                        'tax_amount' => $order->tax_amount,
                        'shipping_amount' => $order->shipping_amount,
                        'total' => $order->total,
                        'total_items' => $order->total_items,
                        'shipping_address' => $order->shipping_address,
                        'created_at' => $order->created_at,
                        'items' => $order->items->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'product_id' => $item->product_id,
                                'product_title' => $item->product_title,
                                'product_sku' => $item->product_sku,
                                'quantity' => $item->quantity,
                                'unit_price' => $item->unit_price,
                                'total_price' => $item->total_price,
                                'size' => $item->size,
                                'chosen_color' => $item->chosen_color,
                                'pieces_per_package' => $item->pieces_per_package,
                                'image' => $item->product?->featured_image_url ?? null,
                            ];
                        }),
                    ];
                }),
            ]
        ], 200);
    }

    public function showByUser(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|string',
            'user_id' => 'required|string',
        ]);

        $order = Order::where('id', $request->order_id)->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        if ((string) $order->user_id !== (string) $request->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found for this user',
            ], 404);
        }

        $order->load(['items', 'user']);

        return response()->json([
            'success' => true,
            'data' => [
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'status_label' => $order->status_label,
                    'subtotal' => $order->subtotal,
                    'tax_amount' => $order->tax_amount,
                    'shipping_amount' => $order->shipping_amount,
                    'total' => $order->total,
                    'total_items' => $order->total_items,
                    'shipping_address' => $order->shipping_address,
                    'billing_address' => $order->billing_address,
                    'notes' => $order->notes,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                    'formatted_total' => $order->formatted_total,
                ],
                'user' => [
                    'id' => $order->user->id,
                    'name' => $order->user->name,
                    'email' => $order->user->email,
                ],
                    'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_title' => $item->product_title,
                        'product_sku' => $item->product_sku,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price,
                        'size' => $item->size,
                        'chosen_color' => $item->chosen_color,
                        'pieces_per_package' => $item->pieces_per_package,
                    ];
                }),
            ]
        ]);
    }

    public function cancelOrder(Request $request, $orderId): JsonResponse
    {
        try {
            $order = Order::find($orderId);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            if ((string) $order->user_id !== (string) Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            if (!$order->canBeCancelled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order cannot be cancelled in its current status.',
                ], 400);
            }

            $order->transitionStatus('cancelled');

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully',
                'data' => [
                    'order' => [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'status_label' => $order->status_label,
                    ],
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to cancel order', [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order.',
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function markDelivered(Request $request, $orderId): JsonResponse
    {
        try {
            $order = Order::find($orderId);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            if ((string) $order->user_id !== (string) Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            if ($order->status !== 'shipped') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order must be in shipped status to mark as delivered.',
                ], 400);
            }

            $order->transitionStatus('delivered');

            return response()->json([
                'success' => true,
                'message' => 'Order marked as delivered',
                'data' => [
                    'order' => [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'status_label' => $order->status_label,
                    ],
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to mark order as delivered', [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark order as delivered.',
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
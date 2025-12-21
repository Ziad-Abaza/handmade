<?php

namespace App\Http\Controllers\Page;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Display a listing of payment methods.
     */
    /**
     * Get all active payment methods.
     * 
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $paymentMethods = PaymentMethod::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'payment_methods' => $paymentMethods
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment methods',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified payment method.
     */
    /**
     * Get payment method details.
     * 
     * @param PaymentMethod $paymentMethod
     * @return JsonResponse
     */
    public function show(PaymentMethod $paymentMethod): JsonResponse
    {
        try {
            if (!$paymentMethod->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment method not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'payment_method' => $paymentMethod
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment method',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process payment.
     */
    /**
     * Process payment.
     * 
     * @param Request $request
     * @param PaymentMethod $paymentMethod
     * @return JsonResponse
     */
    public function process(Request $request, PaymentMethod $paymentMethod): JsonResponse
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                // Add other validation rules as needed
            ]);

            // Process payment based on the payment method
            $result = $this->processPayment($paymentMethod, $validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => [
                    'transaction_id' => $result['transaction_id'] ?? null,
                    'amount' => $validated['amount'],
                    'status' => 'completed'
                ]
            ], 201);
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Process payment based on the payment method.
     */
    /**
     * Process payment based on the payment method.
     * 
     * @param PaymentMethod $paymentMethod
     * @param array $data
     * @return array
     * @throws \Exception
     */
    protected function processPayment(PaymentMethod $paymentMethod, array $data): array
    {
        // Start database transaction for payment processing
        return DB::transaction(function () use ($paymentMethod, $data) {
            // Implement payment processing logic based on the payment method
            // This is a placeholder - implement actual payment gateway integration
            
            $transactionId = 'txn_' . uniqid();
            
            switch ($paymentMethod->code) {
                case 'credit_card':
                    // Process credit card payment
                    // Example: $this->processCreditCardPayment($data);
                    break;
                    
                case 'paypal':
                    // Process PayPal payment
                    // Example: $this->processPayPalPayment($data);
                    break;
                    
                case 'bank_transfer':
                    // Process bank transfer
                    // Example: $this->processBankTransfer($data);
                    break;
                    
                default:
                    throw new \Exception('Unsupported payment method: ' . $paymentMethod->name);
            }
            
            // Record the transaction in the database
            // Example: $transaction = $paymentMethod->transactions()->create([...]);
            
            return [
                'status' => 'completed',
                'transaction_id' => $transactionId,
                'amount' => $data['amount'],
                'payment_method' => $paymentMethod->code
            ];
        });
    }

    /**
     * Show payment success page.
     */
    /**
     * Handle successful payment callback.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function success(Request $request): JsonResponse
    {
        try {
            // Verify the payment success with the payment gateway if needed
            // $this->verifyPayment($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Payment completed successfully',
                'data' => [
                    'redirect_url' => $request->input('redirect_url', url('/'))
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify payment',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Handle failed payment callback.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function failure(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $request->input('message', 'Payment failed'),
            'error_code' => $request->input('error_code'),
            'data' => [
                'retry_url' => $request->input('retry_url')
            ]
        ], 400);
    }
}

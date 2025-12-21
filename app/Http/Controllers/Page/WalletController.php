<?php

namespace App\Http\Controllers\Page;

use App\Http\Controllers\Controller;
use App\Http\Requests\WalletTransactionRequest;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{
    /**
     * Display the user's wallet dashboard.
     */
    public function index(Request $request)
    {
        try {
            $wallet = $request->user()->wallet ?? $this->createWallet($request->user());
            
            $transactions = $wallet->transactions()
                ->latest()
                ->paginate(15);

            return response()->json([
                'status' => true,
                'data' => [
                    'balance' => $wallet->balance,
                    'currency' => $wallet->currency,
                    'transactions' => $transactions,
                    'recent_transactions' => $wallet->transactions()->latest()->take(5)->get()
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to load wallet data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for adding funds to the wallet.
     */
    public function showAddFunds()
    {
        try {
            return response()->json([
                'status' => true,
                'data' => [
                    'min_amount' => config('wallet.min_deposit', 5.00),
                    'max_amount' => config('wallet.max_deposit', 1000.00),
                    'currency' => 'USD' // Can be made dynamic
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to load deposit information.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process adding funds to the wallet.
     */
    public function addFunds(WalletTransactionRequest $request)
    {
        try {
            $wallet = $request->user()->wallet ?? $this->createWallet($request->user());
            $amount = $request->validated('amount');
            $paymentMethod = $request->input('payment_method');

            $transaction = $wallet->deposit(
                $amount,
                'Wallet top-up',
                [
                    'payment_method' => $paymentMethod,
                    'reference' => 'WALLET-' . now()->format('YmdHis'),
                ],
                $request->user() // Pass the authenticated user as the referenceable object
            );

            return response()->json([
                'status' => true,
                'message' => 'Funds added to your wallet successfully!',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'amount' => $amount,
                    'new_balance' => $wallet->fresh()->balance,
                    'payment_method' => $paymentMethod,
                    'reference' => $transaction->reference
                ]
            ]);
                
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to add funds. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for withdrawing funds from the wallet.
     */
    public function showWithdrawFunds()
    {
        try {
            $user = Auth::user();
            $wallet = $user->wallet;
            
            // Create wallet if it doesn't exist
            if (!$wallet) {
                $wallet = $this->createWallet($user);
            }
            
            $maxWithdrawal = min($wallet->balance, config('wallet.max_withdrawal', 5000.00));
            
            return response()->json([
                'status' => true,
                'data' => [
                    'balance' => $wallet->balance,
                    'min_withdrawal' => config('wallet.min_withdrawal', 10.00),
                    'max_withdrawal' => $maxWithdrawal,
                    'currency' => 'USD', // You can make this configurable
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while loading withdrawal information.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process withdrawing funds from the wallet.
     */
    public function withdrawFunds(WalletTransactionRequest $request)
    {
        try {
            $wallet = $request->user()->wallet;
            $amount = $request->validated('amount');
            $paymentMethod = $request->input('payment_method');

            if ($wallet->balance < $amount) {
                return response()->json([
                    'status' => false,
                    'message' => 'Insufficient balance.',
                    'errors' => ['amount' => 'Insufficient balance.']
                ], 422);
            }

            $transaction = $wallet->withdraw(
                $amount,
                'Withdrawal request',
                [
                    'payment_method' => $paymentMethod,
                    'account_details' => $request->input('account_details'),
                ]
            );

            return response()->json([
                'status' => true,
                'message' => 'Withdrawal request submitted successfully!',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'amount' => $amount,
                    'balance' => $wallet->fresh()->balance,
                    'payment_method' => $paymentMethod
                ]
            ]);
                
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to process withdrawal. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display transaction history.
     */
    public function transactions(Request $request)
    {
        try {
            $wallet = $request->user()->wallet;
            
            $transactions = $wallet->transactions()
                ->when($request->type, function ($query) use ($request) {
                    $query->where('type', $request->type);
                })
                ->when($request->date_from, function ($query) use ($request) {
                    $query->whereDate('created_at', '>=', $request->date_from);
                })
                ->when($request->date_to, function ($query) use ($request) {
                    $query->whereDate('created_at', '<=', $request->date_to);
                })
                ->latest()
                ->paginate($request->per_page ?? 20);

            return response()->json([
                'status' => true,
                'data' => [
                    'transactions' => $transactions,
                    'filters' => $request->only(['type', 'date_from', 'date_to']),
                    'balance' => $wallet->balance,
                    'currency' => $wallet->currency
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to load transactions.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a specific transaction.
     */
    public function showTransaction(WalletTransaction $transaction)
    {
        try {
            // check manually auth permission

            if ($transaction->wallet->user_id !== Auth::id()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access to this transaction.'
                ], 403);
            }

            return response()->json([
                'status' => true,
                'data' => [
                    'transaction' => $transaction->load('wallet')
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to load transaction details.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the transfer funds form.
     */
    public function showTransfer()
    {
        try {
            $wallet = Auth::user()->wallet;
            
            return response()->json([
                'status' => true,
                'data' => [
                    'max_transfer' => $wallet->balance,
                    'currency' => $wallet->currency
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to load transfer information.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process transferring funds to another user.
     */
    public function transfer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:' . Auth::user()->wallet->balance,
            ],
            'note' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->email === Auth::user()->email) {
            return response()->json([
                'status' => false,
                'message' => 'You cannot transfer to yourself.',
                'errors' => ['email' => ['You cannot transfer to yourself.']]
            ], 422);
        }

        $recipient = User::where('email', $request->email)->first();
        $recipientWallet = $recipient->wallet ?? $this->createWallet($recipient);
        $senderWallet = Auth::user()->wallet;

        if ($senderWallet->balance < $request->amount) {
            return response()->json([
                'status' => false,
                'message' => 'Insufficient balance.',
                'errors' => ['amount' => ['Insufficient balance.']]
            ], 422);
        }

        try {
            DB::beginTransaction();

            $note = $request->input('note', '');
            $amount = $request->amount;
            $senderEmail = Auth::user()->email;
            $recipientEmail = $recipient->email;

            // Deduct from sender
            $senderTransaction = $senderWallet->withdraw(
                $amount,
                'Transfer to ' . $recipientEmail,
                [
                    'type' => 'transfer_out',
                    'recipient_id' => $recipient->id,
                    'note' => $note,
                ]
            );

            // Add to recipient
            $recipientTransaction = $recipientWallet->deposit(
                $amount,
                'Transfer from ' . $senderEmail,
                [
                    'type' => 'transfer_in',
                    'sender_id' => Auth::id(),
                    'note' => $note,
                ]
            );

            // Link the transactions
            $senderTransaction->update(['reference_id' => $recipientTransaction->id]);
            $recipientTransaction->update(['reference_id' => $senderTransaction->id]);

            DB::commit();

            // Here you might want to send notifications to both users
            
            return response()->json([
                'status' => true,
                'message' => 'Funds transferred successfully!',
                'data' => [
                    'transaction_id' => $senderTransaction->id,
                    'amount' => $amount,
                    'recipient' => [
                        'id' => $recipient->id,
                        'email' => $recipient->email,
                        'name' => $recipient->name
                    ],
                    'new_balance' => $senderWallet->fresh()->balance,
                    'currency' => $senderWallet->currency,
                    'reference' => $senderTransaction->reference
                ]
            ]);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transfer failed: ' . $e->getMessage());
            
            return response()->json([
                'status' => false,
                'message' => 'Transfer failed. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the transfer success page.
     */
    public function transferSuccess()
    {
        return response()->json([
            'status' => true,
            'message' => 'Transfer completed successfully.'
        ]);
    }

    /**
     * Show the deposit success page.
     */
    public function depositSuccess(WalletTransaction $transaction)
    {
        try {
            // check manually auth permission
            if ($transaction->wallet->user_id !== Auth::id()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access to this transaction.'
                ], 403);
            }
            // check if transaction is of type deposit
            if ($transaction->type !== 'deposit') {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid transaction type.'
                ], 400);
            }
            
            return response()->json([
                'status' => true,
                'message' => 'Deposit completed successfully.',
                'data' => [
                    'transaction' => $transaction->load('wallet')
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve deposit information.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the withdrawal success page.
     */
    public function withdrawalSuccess()
    {
        return response()->json([
            'status' => true,
            'message' => 'Withdrawal request submitted successfully.'
        ]);
    }

    /**
     * Create a new wallet for the user if it doesn't exist.
     */
    protected function createWallet(User $user): Wallet
    {
        return $user->wallet()->create([
            'balance' => 0,
            'currency' => 'USD', // Default currency, can be configured
            'is_active' => true,
        ]);
    }
}

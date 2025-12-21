<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wallet extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'balance',
        'currency',
        'is_active',
        'last_activity_at',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'is_active' => 'boolean',
        'last_activity_at' => 'datetime',
    ];

    protected $attributes = [
        'balance' => 0.00,
        'currency' => 'USD',
        'is_active' => true,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function deposit(float $amount, string $description, array $meta = [], $referenceable = null): WalletTransaction
    {
        return $this->createTransaction($amount, 'deposit', $description, $meta, $referenceable);
    }

    public function withdraw(float $amount, string $description, array $meta = [], $referenceable = null): ?WalletTransaction
    {
        if ($this->balance < $amount) {
            return null; // Insufficient balance
        }
        
        return $this->createTransaction(-$amount, 'withdrawal', $description, $meta, $referenceable);
    }

    public function transfer(Wallet $targetWallet, float $amount, string $description, array $meta = [], $referenceable = null): ?array
    {
        if ($this->balance < $amount) {
            return null; // Insufficient balance
        }

        DB::beginTransaction();

        try {
            $withdrawal = $this->createTransaction(-$amount, 'transfer_out', $description, $meta, $referenceable);
            $deposit = $targetWallet->createTransaction($amount, 'transfer_in', $description, array_merge($meta, [
                'source_wallet_id' => $this->id,
            ]), $referenceable);

            DB::commit();

            return [
                'withdrawal' => $withdrawal,
                'deposit' => $deposit,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function createTransaction(float $amount, string $type, string $description, array $meta = [], $referenceable = null): WalletTransaction
    {
        $this->balance += $amount;
        $this->last_activity_at = now();
        $this->save();

        $transactionData = [
            'amount' => $amount,
            'type' => $type,
            'description' => $description,
            'meta' => $meta,
            'balance_after' => $this->balance,
        ];

        // Add referenceable data if provided
        if ($referenceable) {
            $transactionData['referenceable_id'] = $referenceable->id;
            $transactionData['referenceable_type'] = get_class($referenceable);
        } else {
            // If no referenceable is provided, reference the wallet itself
            $transactionData['referenceable_id'] = $this->id;
            $transactionData['referenceable_type'] = get_class($this);
        }

        return $this->transactions()->create($transactionData);
    }
}

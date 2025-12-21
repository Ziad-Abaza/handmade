<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WalletTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'wallet_id',
        'amount',
        'type',
        'description',
        'meta',
        'reference_id',
        'reference_type',
        'referenceable_id',
        'referenceable_type',
        'balance_after',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'meta' => 'array',
        'balance_after' => 'decimal:2',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function reference()
    {
        return $this->morphTo('reference');
    }

    public function isDeposit(): bool
    {
        return $this->amount > 0;
    }

    public function isWithdrawal(): bool
    {
        return $this->amount < 0;
    }

    public function getFormattedAmount(): string
    {
        $sign = $this->isDeposit() ? '+' : '-';
        return $sign . ' ' . number_format(abs($this->amount), 2) . ' ' . ($this->wallet->currency ?? 'USD');
    }
}

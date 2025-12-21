<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->string('type', 20); // deposit, withdrawal, transfer_in, transfer_out, refund, etc.
            $table->string('description');
            $table->json('meta')->nullable(); // Store additional data like payment method, reference, etc.
            $table->decimal('balance_after', 15, 2);
            
            // For tracking related transactions (e.g., transfers between wallets)
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_type')->nullable();
            
            // For tracking what triggered the transaction (order, refund, etc.)
            $table->morphs('referenceable');
            
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('wallet_id');
            $table->index('type');
            $table->index(['reference_id', 'reference_type']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crypto_invoices', function (Blueprint $table) {
            $table->id();
            $table->uuid('order_id');
            $table->string('crypto_currency');
            $table->string('wallet_address');
            $table->decimal('amount_crypto', 20, 8);
            $table->decimal('amount_usd', 10, 2);
            $table->string('status')->default('pending');
            $table->timestamp('expires_at');
            $table->timestamp('confirmed_at')->nullable();
            $table->json('blockchain_data')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->index('order_id');
            $table->index('wallet_address');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crypto_invoices');
    }
};
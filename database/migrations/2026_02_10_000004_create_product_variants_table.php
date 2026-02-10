<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('color_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('size_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('pack_option_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('sku')->unique();
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->integer('stock_qty')->default(0);
            $table->json('attributes')->nullable()->comment('Additional variant attributes');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['product_id', 'color_id', 'size_id', 'pack_option_id'], 'unique_variant_combination');
            $table->index('sku');
            $table->index('price');
            $table->index('sale_price');
            $table->index('is_active');
            $table->index('stock_qty');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
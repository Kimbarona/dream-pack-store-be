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
        Schema::create('banner_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('banner_id')->constrained()->onDelete('cascade');
            $table->string('path')->comment('File path in storage');
            $table->string('disk')->default('public')->comment('Storage disk');
            $table->unsignedInteger('sort_order')->default(0)->comment('Order for display');
            $table->boolean('is_mobile')->default(false)->comment('Whether this is a mobile-specific image');
            $table->timestamps();

            $table->index(['banner_id', 'sort_order']);
            $table->index(['banner_id', 'is_mobile']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banner_images');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add composite indexes for Filament filters and sorting
            $table->index(['status', 'created_at'], 'idx_orders_status_created_at');
            $table->index(['user_id', 'status'], 'idx_orders_user_status');
            $table->index(['created_at'], 'idx_orders_created_at_date');
        });

        Schema::table('products', function (Blueprint $table) {
            // Add indexes for Filament filters and search
            $table->index(['is_active', 'sort_order'], 'idx_products_active_sort_order');
            $table->index(['is_active', 'created_at'], 'idx_products_active_created_at');
        });

        Schema::table('categories', function (Blueprint $table) {
            // Add index for Filament sorting
            $table->index(['is_active', 'sort_order'], 'idx_categories_active_sort_order');
        });

        Schema::table('banners', function (Blueprint $table) {
            // Add index for Filament active filtering
            $table->index(['is_active', 'starts_at', 'ends_at'], 'idx_banners_active_schedule');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_status_created_at');
            $table->dropIndex('idx_orders_user_status');
            $table->dropIndex('idx_orders_created_at_date');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_active_sort_order');
            $table->dropIndex('idx_products_active_created_at');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('idx_categories_active_sort_order');
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->dropIndex('idx_banners_active_schedule');
        });
    }
};
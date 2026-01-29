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
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['status'], 'idx_orders_status');
            $table->index(['user_id'], 'idx_orders_user_id');
            $table->index(['created_at'], 'idx_orders_created_at');
            $table->index(['status', 'created_at'], 'idx_orders_status_date');
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->index(['is_active'], 'idx_banners_is_active');
            $table->index(['sort_order'], 'idx_banners_sort_order');
            $table->index(['is_active', 'sort_order'], 'idx_banners_active_sort');
            $table->index(['starts_at', 'ends_at'], 'idx_banners_schedule');
        });

        Schema::table('banner_images', function (Blueprint $table) {
            $table->index(['banner_id'], 'idx_banner_images_banner_id');
            $table->index(['banner_id', 'sort_order'], 'idx_banner_images_sort');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->index(['slug'], 'idx_categories_slug');
            $table->index(['parent_id'], 'idx_categories_parent');
            $table->index(['is_active', 'sort_order'], 'idx_categories_active_sort');
        });

        Schema::table('category_product', function (Blueprint $table) {
            $table->index(['category_id', 'product_id'], 'idx_category_product');
            $table->index(['product_id', 'category_id'], 'idx_product_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Skip products indexes as they may already exist

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_status');
            $table->dropIndex('idx_orders_user_id');
            $table->dropIndex('idx_orders_created_at');
            $table->dropIndex('idx_orders_status_date');
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->dropIndex('idx_banners_is_active');
            $table->dropIndex('idx_banners_sort_order');
            $table->dropIndex('idx_banners_active_sort');
            $table->dropIndex('idx_banners_schedule');
        });

        Schema::table('banner_images', function (Blueprint $table) {
            $table->dropIndex('idx_banner_images_banner_id');
            $table->dropIndex('idx_banner_images_sort');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('idx_categories_slug');
            $table->dropIndex('idx_categories_parent');
            $table->dropIndex('idx_categories_active_sort');
        });

        Schema::table('category_product', function (Blueprint $table) {
            $table->dropIndex('idx_category_product');
            $table->dropIndex('idx_product_category');
        });
    }
};

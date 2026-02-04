<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index(['is_active', 'sort_order', 'created_at'], 'products_active_sort_index');
            $table->index(['is_active', 'price'], 'products_active_price_index');
            $table->index(['slug'], 'products_slug_index');
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->index(['product_id', 'is_featured', 'sort_order'], 'product_images_featured_index');
        });

        Schema::table('category_product', function (Blueprint $table) {
            $table->index(['category_id', 'product_id'], 'category_product_composite_index');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_active_sort_index');
            $table->dropIndex('products_active_price_index');
            $table->dropIndex('products_slug_index');
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->dropIndex('product_images_featured_index');
        });

        Schema::table('category_product', function (Blueprint $table) {
            $table->dropIndex('category_product_composite_index');
        });
    }
};
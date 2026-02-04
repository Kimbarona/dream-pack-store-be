<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Create indexes with IF NOT EXISTS for Postgres compatibility
        DB::statement('CREATE INDEX IF NOT EXISTS products_active_sort_index ON products (is_active, sort_order, created_at)');
        DB::statement('CREATE INDEX IF NOT EXISTS products_active_price_index ON products (is_active, price)');
        DB::statement('CREATE INDEX IF NOT EXISTS products_slug_index ON products (slug)');
        
        DB::statement('CREATE INDEX IF NOT EXISTS product_images_featured_index ON product_images (product_id, is_featured, sort_order)');
        DB::statement('CREATE INDEX IF NOT EXISTS category_product_composite_index ON category_product (category_id, product_id)');
    }

    public function down()
    {
        // Drop indexes with IF EXISTS for safe rollback
        DB::statement('DROP INDEX IF EXISTS products_active_sort_index');
        DB::statement('DROP INDEX IF EXISTS products_active_price_index');
        DB::statement('DROP INDEX IF EXISTS products_slug_index');
        DB::statement('DROP INDEX IF EXISTS product_images_featured_index');
        DB::statement('DROP INDEX IF EXISTS category_product_composite_index');
    }
};
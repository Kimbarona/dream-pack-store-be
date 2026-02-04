<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create indexes with IF NOT EXISTS for Postgres compatibility
        DB::statement('CREATE INDEX IF NOT EXISTS idx_orders_status ON orders (status)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_orders_user_id ON orders (user_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_orders_created_at ON orders (created_at)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_orders_status_date ON orders (status, created_at)');

        DB::statement('CREATE INDEX IF NOT EXISTS idx_banners_is_active ON banners (is_active)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_banners_sort_order ON banners (sort_order)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_banners_active_sort ON banners (is_active, sort_order)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_banners_schedule ON banners (starts_at, ends_at)');

        DB::statement('CREATE INDEX IF NOT EXISTS idx_banner_images_banner_id ON banner_images (banner_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_banner_images_sort ON banner_images (banner_id, sort_order)');

        DB::statement('CREATE INDEX IF NOT EXISTS idx_categories_slug ON categories (slug)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_categories_parent ON categories (parent_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_categories_active_sort ON categories (is_active, sort_order)');

        DB::statement('CREATE INDEX IF NOT EXISTS idx_category_product ON category_product (category_id, product_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_product_category ON category_product (product_id, category_id)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes with IF EXISTS for safe rollback
        DB::statement('DROP INDEX IF EXISTS idx_orders_status');
        DB::statement('DROP INDEX IF EXISTS idx_orders_user_id');
        DB::statement('DROP INDEX IF EXISTS idx_orders_created_at');
        DB::statement('DROP INDEX IF EXISTS idx_orders_status_date');

        DB::statement('DROP INDEX IF EXISTS idx_banners_is_active');
        DB::statement('DROP INDEX IF EXISTS idx_banners_sort_order');
        DB::statement('DROP INDEX IF EXISTS idx_banners_active_sort');
        DB::statement('DROP INDEX IF EXISTS idx_banners_schedule');

        DB::statement('DROP INDEX IF EXISTS idx_banner_images_banner_id');
        DB::statement('DROP INDEX IF EXISTS idx_banner_images_sort');

        DB::statement('DROP INDEX IF EXISTS idx_categories_slug');
        DB::statement('DROP INDEX IF EXISTS idx_categories_parent');
        DB::statement('DROP INDEX IF EXISTS idx_categories_active_sort');

        DB::statement('DROP INDEX IF EXISTS idx_category_product');
        DB::statement('DROP INDEX IF EXISTS idx_product_category');
    }
};

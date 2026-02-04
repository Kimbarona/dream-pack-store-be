<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Create indexes with IF NOT EXISTS for Postgres compatibility
        DB::statement('CREATE INDEX IF NOT EXISTS idx_orders_status_created_at ON orders (status, created_at)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_orders_user_status ON orders (user_id, status)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_orders_created_at_date ON orders (created_at)');

        DB::statement('CREATE INDEX IF NOT EXISTS idx_products_active_sort_order ON products (is_active, sort_order)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_products_active_created_at ON products (is_active, created_at)');

        DB::statement('CREATE INDEX IF NOT EXISTS idx_categories_active_sort_order ON categories (is_active, sort_order)');
        
        DB::statement('CREATE INDEX IF NOT EXISTS idx_banners_active_schedule ON banners (is_active, starts_at, ends_at)');
    }

    public function down(): void
    {
        // Drop indexes with IF EXISTS for safe rollback
        DB::statement('DROP INDEX IF EXISTS idx_orders_status_created_at');
        DB::statement('DROP INDEX IF EXISTS idx_orders_user_status');
        DB::statement('DROP INDEX IF EXISTS idx_orders_created_at_date');

        DB::statement('DROP INDEX IF EXISTS idx_products_active_sort_order');
        DB::statement('DROP INDEX IF EXISTS idx_products_active_created_at');

        DB::statement('DROP INDEX IF EXISTS idx_categories_active_sort_order');
        
        DB::statement('DROP INDEX IF EXISTS idx_banners_active_schedule');
    }
};
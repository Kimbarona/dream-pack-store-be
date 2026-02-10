<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add is_featured to product_images if not exists
        if (!Schema::hasColumn('product_images', 'is_featured')) {
            Schema::table('product_images', function (Blueprint $table) {
                $table->boolean('is_featured')->default(false)->after('alt_text');
                $table->index('is_featured');
            });
        }
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropIndex(['is_featured']);
            $table->dropColumn('is_featured');
        });
    }
};
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
        Schema::table('banners', function (Blueprint $table) {
            // Add name column if it doesn't exist
            if (!Schema::hasColumn('banners', 'name')) {
                $table->string('name')->after('id')->comment('Banner label like Homepage Hero, Promo Banner');
            }
            
            // Modify existing columns if needed
            $table->string('title')->nullable()->comment('Display title on banner')->change();
            $table->string('subtitle')->nullable()->comment('Display subtitle on banner')->change();
            $table->string('link_url')->nullable()->comment('URL where banner links to')->change();
            $table->boolean('is_active')->default(true)->comment('Whether banner is currently active')->change();
            $table->unsignedInteger('sort_order')->default(0)->comment('Order for display')->change();
            $table->datetime('starts_at')->nullable()->comment('When banner becomes active')->change();
            $table->datetime('ends_at')->nullable()->comment('When banner expires')->change();
            
            // Add indexes if they don't exist
            if (!Schema::hasIndex('banners', 'banners_is_active_sort_order_index')) {
                $table->index(['is_active', 'sort_order']);
            }
            if (!Schema::hasIndex('banners', 'banners_starts_at_ends_at_index')) {
                $table->index(['starts_at', 'ends_at']);
            }
            
            // Remove old image columns if they exist
            if (Schema::hasColumn('banners', 'image_path')) {
                $table->dropColumn('image_path');
            }
            if (Schema::hasColumn('banners', 'image_mobile_path')) {
                $table->dropColumn('image_mobile_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['is_active', 'sort_order']);
            $table->dropIndex(['starts_at', 'ends_at']);
            
            // Remove name column if it exists
            if (Schema::hasColumn('banners', 'name')) {
                $table->dropColumn('name');
            }
        });
    }
};

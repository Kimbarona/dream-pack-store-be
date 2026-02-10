<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('base_description')->nullable()->after('description')->comment('Multilingual description {en, he}');
            $table->decimal('rating', 3, 2)->default(0)->after('pieces_per_package')->comment('Product rating 0.00-5.00');
            $table->integer('sold_count')->default(0)->after('rating')->comment('Number of items sold');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null')->after('sold_count');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['base_description', 'rating', 'sold_count']);
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
};
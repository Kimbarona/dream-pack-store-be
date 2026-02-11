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
        // First, migrate existing data to JSON format
        DB::table('products')->whereNotNull('meta_description')->get()->each(function ($product) {
            if (!empty($product->meta_description)) {
                // Check if it's already JSON (shouldn't be, but just in case)
                $decoded = json_decode($product->meta_description, true);
                if (!$decoded) {
                    // It's plain text, convert to JSON format
                    $newMetaDescription = json_encode([
                        'en' => $product->meta_description,
                        'he' => null // Will be filled later by translation service
                    ]);
                    DB::table('products')
                        ->where('id', $product->id)
                        ->update(['meta_description' => $newMetaDescription]);
                }
            }
        });

        // Now change the column type to JSON
        // For PostgreSQL, we need to handle the type conversion differently
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE products ALTER COLUMN meta_description TYPE JSON USING meta_description::json');
            DB::statement('ALTER TABLE products ALTER COLUMN meta_description DROP NOT NULL');
        } else {
            Schema::table('products', function (Blueprint $table) {
                $table->json('meta_description')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First, get English text from JSON and store it temporarily
        $tempData = [];
        DB::table('products')->whereNotNull('meta_description')->get()->each(function ($product) use (&$tempData) {
            if (!empty($product->meta_description)) {
                $decoded = json_decode($product->meta_description, true);
                if ($decoded && isset($decoded['en'])) {
                    $tempData[$product->id] = $decoded['en'];
                }
            }
        });

        // Change column back to text
        // For PostgreSQL, we need to handle the type conversion differently
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE products ALTER COLUMN meta_description TYPE TEXT USING meta_description::text');
            DB::statement('ALTER TABLE products ALTER COLUMN meta_description DROP NOT NULL');
        } else {
            Schema::table('products', function (Blueprint $table) {
                $table->text('meta_description')->nullable()->change();
            });
        }

        // Restore the English text
        foreach ($tempData as $productId => $englishText) {
            DB::table('products')
                ->where('id', $productId)
                ->update(['meta_description' => $englishText]);
        }
    }
};

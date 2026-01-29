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
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('Setting key for lookup');
            $table->longText('value')->nullable()->comment('Setting value (JSON for complex data)');
            $table->string('type')->default('text')->comment('Data type: text, number, boolean, json, file');
            $table->string('group')->default('general')->comment('Settings group for organization');
            $table->string('title')->comment('Human-readable title');
            $table->text('description')->nullable()->comment('Description of what this setting does');
            $table->boolean('is_public')->default(false)->comment('Whether this setting can be accessed via API');
            $table->integer('sort_order')->default(0)->comment('Order for display in forms');
            $table->timestamps();

            $table->index(['group', 'sort_order']);
            $table->index('key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pack_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('value')->comment('Number of pieces per pack');
            $table->string('label')->comment('Display label like "1 Pack", "2 Pack", etc.');
            $table->string('slug')->unique();
            $table->timestamps();
            
            $table->index('value');
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pack_options');
    }
};
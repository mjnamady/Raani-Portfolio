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
        // Heading and visibility of each page section, keyed like "home.counter" (HOME-12, A-5).
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();
            $table->string('eyebrow', 60)->nullable();
            $table->string('title', 120)->nullable();
            $table->string('subtitle', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};

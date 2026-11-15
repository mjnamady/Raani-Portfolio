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
        // Animated numbers shown across the site (STAT-1); placement says where: banner, counter, highlight.
        Schema::create('stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('value');
            $table->string('suffix', 5)->nullable();
            $table->string('label', 60);
            $table->text('description')->nullable();
            $table->string('placement', 20)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stats');
    }
};

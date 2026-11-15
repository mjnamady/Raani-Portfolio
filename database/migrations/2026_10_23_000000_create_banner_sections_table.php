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
        // Single-row table holding the home page hero (HOME-1).
        Schema::create('banner_sections', function (Blueprint $table) {
            $table->id();
            $table->string('greeting', 60);
            $table->string('name', 80);
            $table->string('role', 80);
            $table->string('biography_heading', 40);
            $table->text('biography');
            // Contact block: moves to Site Settings in M3 so header/footer/contact page share it (GEN-2).
            $table->string('contact_heading', 40);
            $table->string('address', 160)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('image')->nullable();
            $table->string('image_alt', 150);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banner_sections');
    }
};

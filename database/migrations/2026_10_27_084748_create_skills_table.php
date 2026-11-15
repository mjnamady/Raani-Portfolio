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
        // Rows of the home "My Skill" section (HOME-4, SKL-1, SKL-2).
        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->string('icon', 60);
            $table->string('title', 80);
            $table->unsignedInteger('done_count')->default(0);
            $table->string('done_label', 30)->default('Done');
            $table->string('description', 300);
            // The one row that starts with the theme's sliding red background ("current").
            $table->boolean('is_highlighted')->default(false);
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
        Schema::dropIfExists('skills');
    }
};

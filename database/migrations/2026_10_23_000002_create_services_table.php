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
        // Services (SRV-1). The home page cards (HOME-2) use title, icon and project count;
        // excerpt, content and image are filled in by the full Services CRUD in M4.
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('title', 80);
            $table->string('slug', 100)->unique();
            $table->string('icon', 60);
            $table->unsignedInteger('project_count')->default(0);
            $table->string('project_count_label', 30)->default('Projects');
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_featured')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'is_featured', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};

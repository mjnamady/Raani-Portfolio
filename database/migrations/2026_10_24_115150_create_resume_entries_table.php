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
        // Education and experience entries (ABT-2, ABT-3); which columns are used depends on the type.
        Schema::create('resume_entries', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->string('title', 80)->nullable();
            $table->string('organisation', 120)->nullable();
            $table->string('role', 80)->nullable();
            $table->string('period', 40)->nullable();
            $table->string('description', 400);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resume_entries');
    }
};

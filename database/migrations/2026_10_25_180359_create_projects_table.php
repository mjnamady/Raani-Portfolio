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
        // Portfolio projects (PRJ-1): home "Latest Portfolio", the projects page and the details page.
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            // Deleting a category leaves its projects uncategorised.
            $table->foreignId('category_id')->nullable()->constrained('project_categories')->nullOnDelete();
            $table->string('title', 120);
            $table->string('slug', 140)->unique();
            $table->string('tagline', 80)->nullable();
            $table->string('client_name', 80)->nullable();
            $table->date('project_date')->nullable();
            $table->string('card_image')->nullable();
            $table->string('card_image_alt', 150)->nullable();
            $table->string('cover_image')->nullable();
            $table->string('cover_image_alt', 150)->nullable();
            // Sanitized HTML (mews/purifier "rich_text" profile) written in the admin editor.
            $table->text('content')->nullable();
            $table->json('features')->nullable();
            $table->string('external_url', 255)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('meta_title', 70)->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->timestamps();

            $table->index(['status', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};

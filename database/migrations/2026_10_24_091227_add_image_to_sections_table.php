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
        // Optional side image of a section, e.g. the portrait next to the Latest Service list.
        Schema::table('sections', function (Blueprint $table) {
            $table->string('image')->nullable()->after('subtitle');
            $table->string('image_alt', 150)->nullable()->after('image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropColumn(['image', 'image_alt']);
        });
    }
};

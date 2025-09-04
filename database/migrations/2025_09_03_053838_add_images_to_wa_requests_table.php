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
        Schema::table('wa_requests', function (Blueprint $table) {
            $table->json('images')->nullable()->after('description'); // Store image URLs and metadata
            $table->boolean('has_images')->default(false)->after('images'); // Flag to indicate if request has images
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wa_requests', function (Blueprint $table) {
            $table->dropColumn(['images', 'has_images']);
        });
    }
};

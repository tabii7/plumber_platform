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
        Schema::table('plumber_coverages', function (Blueprint $table) {
            $table->string('city')->nullable()->after('hoofdgemeente');
            $table->enum('coverage_type', ['municipality', 'city'])->default('municipality')->after('city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plumber_coverages', function (Blueprint $table) {
            $table->dropColumn(['city', 'coverage_type']);
        });
    }
};

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
            // Drop foreign key first
            $table->dropForeign('plumber_coverages_plumber_id_foreign');
            // Drop unique constraint
            $table->dropUnique('plumber_coverages_plumber_id_hoofdgemeente_unique');
            // Recreate foreign key
            $table->foreign('plumber_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plumber_coverages', function (Blueprint $table) {
            // Drop foreign key
            $table->dropForeign('plumber_coverages_plumber_id_foreign');
            // Recreate unique constraint
            $table->unique(['plumber_id', 'hoofdgemeente'], 'plumber_coverages_plumber_id_hoofdgemeente_unique');
            // Recreate foreign key
            $table->foreign('plumber_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};

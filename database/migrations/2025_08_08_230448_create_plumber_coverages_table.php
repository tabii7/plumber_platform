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
        Schema::create('plumber_coverages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plumber_id')->constrained('users')->onDelete('cascade');
            $table->string('hoofdgemeente', 255)->index();
            $table->timestamps();
            $table->unique(['plumber_id', 'hoofdgemeente']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plumber_coverages');
    }
};

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
    Schema::disableForeignKeyConstraints();

    Schema::create('plumber_postal_code', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('plumber_id');
        $table->unsignedBigInteger('postal_code_id');
        $table->timestamps();

        $table->foreign('plumber_id')->references('id')->on('users')->onDelete('cascade');
        $table->foreign('postal_code_id')->references('id')->on('postal_codes')->onDelete('cascade');
    });

    Schema::enableForeignKeyConstraints();
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plumber_postal_code');
    }
};

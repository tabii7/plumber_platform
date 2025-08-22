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
        Schema::create('wa_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plumber_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('request_id')->constrained('wa_requests')->onDelete('cascade');
            $table->text('personal_message');
            $table->string('status')->default('pending'); // pending, selected, rejected
            $table->integer('eta_minutes')->nullable();
            $table->decimal('distance_km', 5, 2)->nullable();
            $table->decimal('rating', 3, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wa_offers');
    }
};

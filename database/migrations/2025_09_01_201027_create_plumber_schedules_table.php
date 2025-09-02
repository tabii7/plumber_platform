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
        Schema::create('plumber_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('timezone')->default('Europe/Brussels');
            $table->json('schedule_data'); // Stores the complete schedule structure
            $table->json('holidays')->nullable(); // Array of holiday dates
            $table->json('vacations')->nullable(); // Array of vacation periods
            $table->timestamp('last_updated')->nullable();
            $table->timestamps();
            
            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plumber_schedules');
    }
};

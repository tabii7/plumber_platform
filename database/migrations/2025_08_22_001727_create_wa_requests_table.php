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
        Schema::create('wa_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->string('problem'); // Leak, Blockage, Heating, etc.
            $table->string('urgency'); // high, normal, later
            $table->text('description');
            $table->string('status')->default('broadcasting'); // broadcasting, active, completed, cancelled
            $table->foreignId('selected_plumber_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('completed_at')->nullable();
            $table->integer('rating')->nullable(); // 1-5 rating
            $table->text('rating_comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wa_requests');
    }
};

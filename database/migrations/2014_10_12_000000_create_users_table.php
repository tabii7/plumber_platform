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
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('full_name');
        $table->string('phone')->nullable();
        $table->string('whatsapp_number')->nullable();
        $table->string('address')->nullable();
        $table->string('number')->nullable();
        $table->string('postal_code')->nullable();
        $table->string('city')->nullable();
        $table->string('country')->nullable();
        $table->enum('role', ['client', 'plumber', 'admin'])->default('client');
        $table->string('btw_number')->nullable();
        $table->text('werk_radius')->nullable();
        $table->string('email')->unique();
        $table->string('password');
        $table->string('conversation_state')->nullable();
        $table->string('last_selected_service')->nullable();
        $table->text('last_description')->nullable();
        $table->rememberToken();
        $table->timestamps();
    });
}

    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

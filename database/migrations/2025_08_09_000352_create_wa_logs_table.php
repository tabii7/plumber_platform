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
        Schema::create('wa_logs', function (Blueprint $table) {
            $table->id();
            $table->string('wa_number')->index();
            $table->enum('direction', ['in','out']);
            $table->json('payload_json');                 // whatever we sent/received
            $table->string('status')->nullable();         // e.g. queued/sent/recv
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wa_logs');
    }
};

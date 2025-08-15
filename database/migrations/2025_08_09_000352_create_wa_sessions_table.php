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
        Schema::create('wa_sessions', function (Blueprint $table) {
             $table->id();
            $table->string('wa_number')->index();          // '32470123456'
            $table->unsignedBigInteger('user_id')->nullable()->index(); // optional link to users
            $table->string('flow_code')->index();          // e.g. 'client_flow'
            $table->string('node_code')->nullable();       // current node in the flow
            $table->json('context_json')->nullable();      // scratchpad (category, description, etc.)
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wa_sessions');
    }
};

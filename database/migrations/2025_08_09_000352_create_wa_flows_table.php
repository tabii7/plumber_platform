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
        Schema::create('wa_flows', function (Blueprint $table) {
         $table->id();
            $table->string('code')->unique();            // e.g. 'client_flow'
            $table->string('name');
            $table->string('entry_keyword')->nullable(); // e.g. 'info' / 'plumber'
            $table->enum('target_role', ['client', 'plumber', 'any'])->default('any');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wa_flows');
    }
};

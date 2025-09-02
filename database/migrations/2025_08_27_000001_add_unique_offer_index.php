<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('wa_offers', function (Blueprint $table) {
            if (!Schema::hasColumn('wa_offers', 'plumber_id')) {
                // assume column exists in your schema; otherwise add it first
            }
            $table->unique(['request_id', 'plumber_id'], 'wa_offers_request_plumber_unique');
        });
    }
    public function down(): void {
        Schema::table('wa_offers', function (Blueprint $table) {
            $table->dropUnique('wa_offers_request_plumber_unique');
        });
    }
};

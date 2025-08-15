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
        Schema::create('wa_nodes', function (Blueprint $table) {
         $table->id();
            $table->unsignedBigInteger('flow_id')->index();   // no FK constraint
            $table->string('code');                            // unique within flow
            $table->enum('type', ['text','buttons','list','collect_text','dispatch'])->default('text');
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->string('footer')->nullable();
            $table->json('options_json')->nullable();          // buttons/list config
            $table->json('next_map_json')->nullable();         // reply -> next-node map
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['flow_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wa_nodes');
    }
};

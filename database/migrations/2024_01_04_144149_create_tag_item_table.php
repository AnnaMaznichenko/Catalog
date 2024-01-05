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
        Schema::create('tag_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId("id_tag")
                ->references("id")
                ->on("tags")
                ->onDelete("cascade");
            $table->foreignId("id_item")
                ->references("id")
                ->on("items")
                ->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tag_item');
    }
};

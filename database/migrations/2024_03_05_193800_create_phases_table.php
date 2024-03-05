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
        Schema::create('phases', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('timeline_id');
            $table->unsignedBigInteger('title_translation_id');
            $table->string('color');

            $table->foreign('timeline_id')->references('id')->on('timelines');
            $table->foreign('title_translation_id')->references('id')->on('translations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('phases', function (Blueprint $table) {
            $table->dropForeign(['timeline_id']);
            $table->dropForeign(['title_translation_id']);
        });

        Schema::dropIfExists('phases');
    }
};

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
        Schema::create('waypoints', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('phase_id');
            $table->unsignedBigInteger('article_id');
            $table->unsignedBigInteger('title_translation_id');
            $table->string('location');
            $table->string('image_source')->nullable();
            $table->boolean('is_bound');

            $table->foreign('phase_id')->references('id')->on('phases');
            $table->foreign('article_id')->references('id')->on('articles');
            $table->foreign('title_translation_id')->references('id')->on('translations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('waypoints', function (Blueprint $table) {
            $table->dropForeign(['phase_id']);
            $table->dropForeign(['article_id']);
            $table->dropForeign(['title_translation_id']);
        });

        Schema::dropIfExists('waypoints');
    }
};

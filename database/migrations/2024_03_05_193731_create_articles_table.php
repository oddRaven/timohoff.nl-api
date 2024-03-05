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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('title_translation_id');
            $table->unsignedBigInteger('text_translation_id');

            $table->foreign('title_translation_id')->references('id')->on('translations');
            $table->foreign('text_translation_id')->references('id')->on('translations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['title_translation_id']);
            $table->dropForeign(['text_translation_id']);
        });

        Schema::dropIfExists('articles');
    }
};

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
        Schema::create('language', function(Blueprint $table){
            $table->string('code', 2)->unique();
            $table->string('name')->unique();

            $table->primary(['code', 'name']);
        });

        Schema::create('translation', function(Blueprint $table){
            $table->id();
            $table->string('title');
            $table->timestamps();
        });

        Schema::create('language_translation', function(Blueprint $table){
            $table->unsignedBigInteger('translation_id');
            $table->char('language_code', 2);
            $table->longText('text');
            $table->timestamps();

            $table->primary(['translation_id', 'language_code']);
            $table->foreign('translation_id')->references('id')->on('translation');
            $table->foreign('language_code')->references('code')->on('language');
        });

        Schema::create('article', function(Blueprint $table){
            $table->id();
            $table->unsignedBigInteger('title_translation_id');
            $table->unsignedBigInteger('text_translation_id');

            $table->foreign('title_translation_id')->references('id')->on('translation');
            $table->foreign('text_translation_id')->references('id')->on('translation');
        });

        Schema::create('timeline', function(Blueprint $table){
            $table->id();
            $table->string('title');
        });

        Schema::create('phase', function(Blueprint $table){
            $table->id();
            $table->unsignedBigInteger('timeline_id');
            $table->unsignedBigInteger('title_translation_id');
            $table->string('color');

            $table->foreign('timeline_id')->references('id')->on('timeline');
            $table->foreign('title_translation_id')->references('id')->on('translation');
        });

        Schema::create('waypoint', function(Blueprint $table){
            $table->id();
            $table->unsignedBigInteger('phase_id');
            $table->unsignedBigInteger('article_id');
            $table->unsignedBigInteger('title_translation_id');
            $table->string('location');
            $table->string('image_source')->nullable();
            $table->boolean('is_bound');

            $table->foreign('phase_id')->references('id')->on('phase');
            $table->foreign('article_id')->references('id')->on('article');
            $table->foreign('title_translation_id')->references('id')->on('translation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void 
    {
        Schema::table('language_translation', function (Blueprint $table) {
            $table->dropForeign(['translation_id']);
            $table->dropForeign(['language_code']);
        });

        Schema::table('article', function (Blueprint $table) {
            $table->dropForeign(['title_translation_id']);
            $table->dropForeign(['text_translation_id']);
        });

        Schema::table('phase', function (Blueprint $table) {
            $table->dropForeign(['timeline_id']);
            $table->dropForeign(['title_translation_id']);
        });

        Schema::table('waypoint', function (Blueprint $table) {
            $table->dropForeign(['phase_id']);
            $table->dropForeign(['article_id']);
            $table->dropForeign(['title_translation_id']);
        });

        Schema::dropIfExists('article');
        Schema::dropIfExists('language');
        Schema::dropIfExists('translation');
        Schema::dropIfExists('language_translation');
        Schema::dropIfExists('timeline');
        Schema::dropIfExists('phase');
        Schema::dropIfExists('waypoint');
    }
};

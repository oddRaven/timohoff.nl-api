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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('profile_collection_id');
            $table->unsignedBigInteger('article_id');
            $table->unsignedBigInteger('title_translation_id');
            $table->string('image_source')->nullable();

            $table->foreign('profile_collection_id')->references('id')->on('profile_collections')->onDelete('cascade');
            $table->foreign('article_id')->references('id')->on('articles');
            $table->foreign('title_translation_id')->references('id')->on('translations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropForeign(['profile_collection_id']);
            $table->dropForeign(['article_id']);
            $table->dropForeign(['title_translation_id']);
        });

        Schema::dropIfExists('profiles');
    }
};

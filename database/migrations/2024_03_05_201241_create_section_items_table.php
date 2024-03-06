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
        Schema::create('section_items', function (Blueprint $table) {
            $table->unsignedBigInteger('item_id');
            $table->string('item_type');
            $table->timestamps();
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('title_translation_id');
            $table->integer('order')->default(0);

            $table->primary('item_id', 'item_type');
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');
            $table->foreign('title_translation_id')->references('id')->on('translations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('section_items', function (Blueprint $table) {
            $table->dropForeign(['section_id']);
            $table->dropForeign(['title_translation_id']);
        });

        Schema::dropIfExists('section_items');
    }
};

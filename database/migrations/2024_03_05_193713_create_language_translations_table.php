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
        Schema::create('language_translations', function (Blueprint $table) {
            $table->unsignedBigInteger('translation_id');
            $table->char('language_code', 2);
            $table->timestamps();
            $table->longText('text')->nullable();

            $table->primary(['translation_id', 'language_code']);
            $table->foreign('translation_id')->references('id')->on('translations');
            $table->foreign('language_code')->references('code')->on('languages');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('language_translations', function (Blueprint $table) {
            $table->dropForeign(['translation_id']);
            $table->dropForeign(['language_code']);
        });

        Schema::dropIfExists('language_translations');
    }
};

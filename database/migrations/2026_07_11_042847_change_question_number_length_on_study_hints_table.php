<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('study_hints', function (Blueprint $table) {
            $table->string('question_no_1', 4)->nullable()->change();
            $table->string('question_no_2', 4)->nullable()->change();
            $table->string('question_no_3', 4)->nullable()->change();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('study_hints', function (Blueprint $table) {
            $table->string('question_no_1', 1)->nullable()->change();
            $table->string('question_no_2', 1)->nullable()->change();
            $table->string('question_no_3', 1)->nullable()->change();
        });
    }
};
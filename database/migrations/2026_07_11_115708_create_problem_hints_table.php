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
        Schema::create('problem_hints', function (Blueprint $table) {
            $table->id();

            $table->foreignId('study_hint_id')
                ->constrained('study_hints')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('hint_order');

            $table->text('content')->nullable();
            $table->text('image_url')->nullable();
            $table->string('image_path')->nullable();

            $table->timestamps();

            $table->unique([
                'study_hint_id',
                'hint_order',
            ]);
        });
    }    /**
         * Reverse the migrations.
         */
    public function down(): void
    {
        Schema::dropIfExists('problem_hints');
    }
};
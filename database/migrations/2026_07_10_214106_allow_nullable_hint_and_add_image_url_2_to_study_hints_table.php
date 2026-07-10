<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('study_hints', function (Blueprint $table) {
            $table->text('hint')->nullable()->change();

            $table->text('image_url_2')
                ->nullable()
                ->after('image_url');
        });
    }

    public function down(): void
    {
        Schema::table('study_hints', function (Blueprint $table) {
            $table->dropColumn('image_url_2');
            $table->text('hint')->nullable(false)->change();
        });
    }
};
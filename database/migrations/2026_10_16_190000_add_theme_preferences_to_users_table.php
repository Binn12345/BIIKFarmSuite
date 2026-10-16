<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'theme_background')) {
                $table->string('theme_background', 30)->nullable();
            }
            if (! Schema::hasColumn('users', 'theme_accent')) {
                $table->string('theme_accent', 20)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'theme_background')) {
                $table->dropColumn('theme_background');
            }
            if (Schema::hasColumn('users', 'theme_accent')) {
                $table->dropColumn('theme_accent');
            }
        });
    }
};

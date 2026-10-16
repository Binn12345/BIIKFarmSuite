<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'profile_picture')) {
                $table->binary('profile_picture')->nullable();
            }
            if (! Schema::hasColumn('users', 'profile_picture_mime_type')) {
                $table->string('profile_picture_mime_type', 100)->nullable();
            }
            if (! Schema::hasColumn('users', 'locale')) {
                $table->string('locale', 5)->nullable()->index();
            }
        });
        if (DB::getDriverName() === 'mysql' && Schema::hasColumn('users', 'profile_picture')) {
            DB::statement('ALTER TABLE users MODIFY profile_picture LONGBLOB NULL');
        }
    }

    public function down(): void
    {
        // Profile images and language preferences are retained on rollback.
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pens')) {
            Schema::create('pens', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100)->unique();
                $table->unsignedInteger('capacity')->default(1);
                $table->string('status', 20)->default('active')->index();
                $table->string('location')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        } else {
            Schema::table('pens', function (Blueprint $table): void {
                if (! Schema::hasColumn('pens', 'location')) {
                    $table->string('location')->nullable();
                }
                if (! Schema::hasColumn('pens', 'notes')) {
                    $table->text('notes')->nullable();
                }
                if (! Schema::hasColumn('pens', 'created_by')) {
                    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('pens', 'updated_by')) {
                    $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                }
            });
        }

        if (! Schema::hasTable('animals')) {
            Schema::create('animals', function (Blueprint $table) {
                $table->id();
                $table->string('tag_number', 100)->unique();
                $table->string('name', 100)->nullable();
                $table->string('breed', 100)->nullable();
                $table->string('sex', 16);
                $table->date('birth_date')->nullable();
                $table->string('status', 20)->default('active')->index();
                $table->foreignId('pen_id')->nullable()->constrained('pens')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->index(['pen_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        // Preserve existing farm records if this additive schema migration is rolled back.
    }
};

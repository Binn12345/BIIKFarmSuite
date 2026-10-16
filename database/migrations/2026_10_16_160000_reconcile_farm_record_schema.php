<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pens')) {
            Schema::create('pens', function (Blueprint $table): void {
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
        }

        if (! Schema::hasTable('animals')) {
            Schema::create('animals', function (Blueprint $table): void {
                $table->id();
                $table->string('tag_number', 100)->unique();
                $table->string('name', 100)->nullable();
                $table->string('breed', 100)->nullable();
                $table->string('category', 100)->nullable();
                $table->string('sex', 16);
                $table->date('birth_date')->nullable();
                $table->string('acquisition_source', 150)->nullable();
                $table->date('acquired_at')->nullable();
                $table->decimal('purchase_cost', 12, 2)->nullable();
                $table->string('status', 20)->default('active')->index();
                $table->foreignId('pen_id')->nullable()->constrained('pens')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->index(['pen_id', 'status']);
            });
        }

        if (! Schema::hasTable('farm_records')) {
            return;
        }

        Schema::table('farm_records', function (Blueprint $table): void {
            if (! Schema::hasIndex('farm_records', ['module', 'reference'])) {
                $table->unique(['module', 'reference']);
            }
            if (! Schema::hasIndex('farm_records', ['module', 'record_date'])) {
                $table->index(['module', 'record_date']);
            }
            if (! Schema::hasIndex('farm_records', ['module', 'status'])) {
                $table->index(['module', 'status']);
            }
            if (! Schema::hasIndex('farm_records', ['module', 'related_animal_id'])) {
                $table->index(['module', 'related_animal_id']);
            }
        });

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $foreignKeys = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'farm_records')
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->pluck('COLUMN_NAME')
            ->all();

        Schema::table('farm_records', function (Blueprint $table) use ($foreignKeys): void {
            if (! in_array('related_animal_id', $foreignKeys, true)) {
                $table->foreign('related_animal_id')->references('id')->on('animals')->restrictOnDelete();
            }
            if (! in_array('related_record_id', $foreignKeys, true)) {
                $table->foreign('related_record_id')->references('id')->on('farm_records')->restrictOnDelete();
            }
            if (! in_array('created_by', $foreignKeys, true)) {
                $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            }
            if (! in_array('updated_by', $foreignKeys, true)) {
                $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        // Do not remove operational tables, constraints, or records during rollback.
    }
};

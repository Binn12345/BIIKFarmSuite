<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('farm_records')) {
            return;
        }

        Schema::create('farm_records', function (Blueprint $table): void {
            $table->id();
            $table->string('module', 40);
            $table->string('reference', 100)->nullable();
            $table->string('title', 255)->nullable();
            $table->date('record_date')->nullable()->index();
            $table->string('status', 40)->default('active')->index();
            $table->decimal('quantity', 14, 3)->nullable();
            $table->decimal('weight', 10, 3)->nullable();
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->decimal('amount', 14, 2)->nullable();
            $table->string('unit', 40)->nullable();
            $table->string('category', 100)->nullable();
            $table->foreignId('related_animal_id')->nullable()->constrained('animals')->restrictOnDelete();
            $table->foreignId('related_record_id')->nullable()->constrained('farm_records')->restrictOnDelete();
            $table->json('data')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['module', 'reference']);
            $table->index(['module', 'record_date']);
            $table->index(['module', 'status']);
            $table->index(['module', 'related_animal_id']);
        });
    }

    public function down(): void
    {
        // This table may contain operational records; rollback must not destroy them.
    }
};

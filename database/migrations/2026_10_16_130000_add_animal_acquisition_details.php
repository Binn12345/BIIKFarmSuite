<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('animals')) {
            return;
        }

        Schema::table('animals', function (Blueprint $table): void {
            if (! Schema::hasColumn('animals', 'category')) {
                $table->string('category', 100)->nullable();
            }
            if (! Schema::hasColumn('animals', 'acquisition_source')) {
                $table->string('acquisition_source', 150)->nullable();
            }
            if (! Schema::hasColumn('animals', 'acquired_at')) {
                $table->date('acquired_at')->nullable();
            }
            if (! Schema::hasColumn('animals', 'purchase_cost')) {
                $table->decimal('purchase_cost', 12, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        // Preserve operational animal data on rollback.
    }
};

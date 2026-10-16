<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('stock_movements')) {
            Schema::create('stock_movements', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('item_record_id')->constrained('farm_records')->restrictOnDelete();
                $table->string('movement_type', 30);
                $table->decimal('quantity', 14, 3);
                $table->decimal('balance_after', 14, 3);
                $table->string('reference', 100)->nullable();
                $table->foreignId('source_record_id')->nullable()->constrained('farm_records')->nullOnDelete();
                $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('created_at')->useCurrent()->index();
                $table->index(['item_record_id', 'created_at']);
                $table->index(['movement_type', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        // Stock movement history is retained on rollback.
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sale_payments')) {
            return;
        }

        Schema::create('sale_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sale_id')->constrained('farm_records')->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('method', 50);
            $table->string('reference', 100)->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent()->index();
            $table->index(['sale_id', 'created_at']);
        });
    }

    public function down(): void
    {
        // Financial payment history must survive a migration rollback.
    }
};

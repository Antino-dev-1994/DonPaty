<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_periods', function (Blueprint $table): void {
            $table->ulid('id')->primary(); $table->unsignedSmallInteger('year'); $table->unsignedTinyInteger('month'); $table->string('status')->index();
            $table->decimal('processed_flour_quantity', 20, 6)->default(0); $table->timestamp('opened_at')->nullable(); $table->foreignId('opened_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('closed_at')->nullable(); $table->foreignId('closed_by')->nullable()->constrained('users')->restrictOnDelete(); $table->timestamp('reopened_at')->nullable(); $table->foreignId('reopened_by')->nullable()->constrained('users')->restrictOnDelete(); $table->timestamps();
            $table->unique(['year', 'month']);
        });
        Schema::create('utility_cost_records', function (Blueprint $table): void {
            $table->ulid('id')->primary(); $table->foreignUlid('cost_period_id')->constrained('cost_periods')->cascadeOnDelete(); $table->string('utility_type');
            $table->date('billed_from'); $table->date('billed_to'); $table->date('paid_at'); $table->unsignedBigInteger('total_amount');
            $table->decimal('business_percentage', 7, 4); $table->decimal('household_percentage', 7, 4); $table->unsignedBigInteger('business_amount'); $table->unsignedBigInteger('household_amount');
            $table->decimal('physical_consumption', 20, 6)->nullable(); $table->string('physical_consumption_unit')->nullable(); $table->string('reference')->nullable(); $table->timestamps();
            $table->unique(['cost_period_id', 'utility_type']);
        });
        Schema::create('overhead_rates', function (Blueprint $table): void {
            $table->ulid('id')->primary(); $table->foreignUlid('cost_period_id')->constrained('cost_periods')->cascadeOnDelete(); $table->string('cost_type');
            $table->unsignedBigInteger('suggested_rate')->nullable(); $table->unsignedBigInteger('manual_rate')->nullable(); $table->unsignedBigInteger('effective_rate');
            $table->decimal('calculation_base_quantity', 20, 6)->default(0); $table->string('method'); $table->text('override_reason')->nullable(); $table->foreignId('set_by')->constrained('users')->restrictOnDelete(); $table->timestamps();
            $table->unique(['cost_period_id', 'cost_type']);
        });
        Schema::create('cost_pool_entries', function (Blueprint $table): void {
            $table->ulid('id')->primary(); $table->foreignUlid('cost_period_id')->constrained('cost_periods')->cascadeOnDelete(); $table->string('cost_type')->index(); $table->unsignedBigInteger('amount'); $table->nullableMorphs('source'); $table->dateTime('effective_at')->index(); $table->timestamps();
        });
        Schema::create('cost_allocations', function (Blueprint $table): void {
            $table->ulid('id')->primary(); $table->foreignUlid('cost_period_id')->constrained('cost_periods')->restrictOnDelete(); $table->ulid('production_order_id')->index(); $table->string('cost_type'); $table->decimal('base_quantity', 20, 6); $table->unsignedBigInteger('rate'); $table->unsignedBigInteger('amount'); $table->timestamps();
            $table->unique(['production_order_id', 'cost_type']);
        });
        Schema::create('cost_variances', function (Blueprint $table): void {
            $table->ulid('id')->primary(); $table->foreignUlid('cost_period_id')->constrained('cost_periods')->cascadeOnDelete(); $table->string('cost_type'); $table->unsignedBigInteger('actual_amount'); $table->unsignedBigInteger('allocated_amount'); $table->bigInteger('variance_amount'); $table->timestamps();
            $table->unique(['cost_period_id', 'cost_type']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('cost_variances'); Schema::dropIfExists('cost_allocations'); Schema::dropIfExists('cost_pool_entries'); Schema::dropIfExists('overhead_rates'); Schema::dropIfExists('utility_cost_records'); Schema::dropIfExists('cost_periods');
    }
};

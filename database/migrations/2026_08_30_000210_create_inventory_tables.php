<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_sequences', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('document_type');
            $table->string('period');
            $table->unsignedBigInteger('last_number')->default(0);
            $table->timestamps();
            $table->unique(['document_type', 'period']);
        });

        Schema::create('inventory_movements', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('document_number')->unique();
            $table->string('movement_type')->index();
            $table->timestamp('effective_at')->index();
            $table->nullableMorphs('source');
            $table->string('status')->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignUlid('authorization_request_id')->nullable()->constrained('authorization_requests')->nullOnDelete();
            $table->foreignUlid('reversal_of_id')->nullable()->constrained('inventory_movements')->restrictOnDelete();
            $table->timestamps();
            $table->index(['source_type', 'source_id']);
        });

        Schema::create('inventory_movement_lines', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('inventory_movement_id')->constrained('inventory_movements')->cascadeOnDelete();
            $table->foreignUlid('presentation_id')->constrained('product_presentations')->restrictOnDelete();
            $table->decimal('quantity_in', 20, 6)->default(0);
            $table->decimal('quantity_out', 20, 6)->default(0);
            $table->unsignedBigInteger('unit_cost');
            $table->unsignedBigInteger('total_cost');
            $table->decimal('balance_before', 20, 6);
            $table->decimal('balance_after', 20, 6);
            $table->timestamps();
            $table->index(['presentation_id', 'created_at']);
        });

        Schema::create('inventory_balances', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('presentation_id')->unique()->constrained('product_presentations')->cascadeOnDelete();
            $table->decimal('physical_quantity', 20, 6)->default(0);
            $table->decimal('reserved_quantity', 20, 6)->default(0);
            $table->unsignedBigInteger('average_unit_cost')->default(0);
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('inventory_adjustments', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('document_number')->unique();
            $table->string('adjustment_type')->default('manual');
            $table->timestamp('effective_at');
            $table->string('status')->index();
            $table->text('reason');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignUlid('inventory_movement_id')->nullable()->constrained('inventory_movements')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('inventory_adjustment_lines', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('inventory_adjustment_id')->constrained('inventory_adjustments')->cascadeOnDelete();
            $table->foreignUlid('presentation_id')->constrained('product_presentations')->restrictOnDelete();
            $table->decimal('expected_quantity', 20, 6);
            $table->decimal('counted_quantity', 20, 6);
            $table->decimal('difference_quantity', 20, 6);
            $table->unsignedBigInteger('unit_cost')->nullable();
            $table->timestamps();
            $table->unique(['inventory_adjustment_id', 'presentation_id']);
        });

        Schema::create('package_conversions', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('document_number')->unique();
            $table->string('conversion_type');
            $table->foreignUlid('package_presentation_id')->constrained('product_presentations')->restrictOnDelete();
            $table->decimal('package_quantity', 20, 6);
            $table->unsignedBigInteger('total_cost');
            $table->nullableMorphs('source');
            $table->string('status')->index();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignUlid('inventory_movement_id')->nullable()->constrained('inventory_movements')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('negative_stock_incidents', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('presentation_id')->constrained('product_presentations')->restrictOnDelete();
            $table->foreignUlid('inventory_movement_line_id')->constrained('inventory_movement_lines')->cascadeOnDelete();
            $table->foreignUlid('authorization_request_id')->constrained('authorization_requests')->restrictOnDelete();
            $table->decimal('negative_quantity', 20, 6);
            $table->unsignedBigInteger('estimated_unit_cost');
            $table->string('status')->default('pending')->index();
            $table->timestamp('regularized_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('negative_stock_incidents');
        Schema::dropIfExists('package_conversions');
        Schema::dropIfExists('inventory_adjustment_lines');
        Schema::dropIfExists('inventory_adjustments');
        Schema::dropIfExists('inventory_balances');
        Schema::dropIfExists('inventory_movement_lines');
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('document_sequences');
    }
};

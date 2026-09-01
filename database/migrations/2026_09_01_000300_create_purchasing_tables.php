<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_profiles', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('person_id')->unique()->constrained('people')->restrictOnDelete();
            $table->string('trade_name')->nullable();
            $table->string('tax_identifier')->nullable()->index();
            $table->unsignedSmallInteger('default_payment_term_days')->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('purchases', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('document_number')->unique();
            $table->foreignUlid('supplier_person_id')->constrained('people')->restrictOnDelete();
            $table->string('supplier_document_number')->nullable();
            $table->date('issued_at')->index();
            $table->date('due_at')->nullable()->index();
            $table->string('payment_condition')->index();
            $table->string('status')->index();
            $table->string('receipt_status')->index();
            $table->string('payment_status')->index();
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('additional_costs')->default(0);
            $table->unsignedBigInteger('total');
            $table->unsignedBigInteger('paid_amount')->default(0);
            $table->unsignedBigInteger('balance_amount');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignUlid('reversal_of_id')->nullable()->constrained('purchases')->restrictOnDelete();
            $table->timestamps();
            $table->index(['supplier_person_id', 'status']);
        });

        Schema::create('purchase_lines', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignUlid('presentation_id')->constrained('product_presentations')->restrictOnDelete();
            $table->decimal('ordered_quantity', 20, 6);
            $table->decimal('received_quantity', 20, 6)->default(0);
            $table->decimal('returned_quantity', 20, 6)->default(0);
            $table->unsignedBigInteger('unit_price');
            $table->unsignedBigInteger('allocated_additional_cost')->default(0);
            $table->unsignedBigInteger('line_total');
            $table->timestamps();
            $table->unique(['purchase_id', 'presentation_id']);
        });

        Schema::create('purchase_receipts', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('document_number')->unique();
            $table->foreignUlid('purchase_id')->constrained('purchases')->restrictOnDelete();
            $table->dateTime('received_at')->index();
            $table->string('status')->index();
            $table->foreignId('received_by')->constrained('users')->restrictOnDelete();
            $table->foreignUlid('inventory_movement_id')->nullable()->unique()->constrained('inventory_movements')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_receipt_lines', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('purchase_receipt_id')->constrained('purchase_receipts')->cascadeOnDelete();
            $table->foreignUlid('purchase_line_id')->constrained('purchase_lines')->restrictOnDelete();
            $table->decimal('quantity', 20, 6);
            $table->unsignedBigInteger('unit_cost');
            $table->unsignedBigInteger('total_cost');
            $table->timestamps();
            $table->unique(['purchase_receipt_id', 'purchase_line_id']);
        });

        Schema::create('purchase_returns', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('document_number')->unique();
            $table->foreignUlid('purchase_id')->constrained('purchases')->restrictOnDelete();
            $table->dateTime('returned_at')->index();
            $table->string('status')->index();
            $table->unsignedBigInteger('total_amount');
            $table->foreignUlid('inventory_movement_id')->nullable()->unique()->constrained('inventory_movements')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->text('reason');
            $table->timestamps();
        });

        Schema::create('purchase_return_lines', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('purchase_return_id')->constrained('purchase_returns')->cascadeOnDelete();
            $table->foreignUlid('purchase_line_id')->constrained('purchase_lines')->restrictOnDelete();
            $table->decimal('quantity', 20, 6);
            $table->unsignedBigInteger('unit_cost');
            $table->unsignedBigInteger('total_cost');
            $table->timestamps();
            $table->unique(['purchase_return_id', 'purchase_line_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_return_lines');
        Schema::dropIfExists('purchase_returns');
        Schema::dropIfExists('purchase_receipt_lines');
        Schema::dropIfExists('purchase_receipts');
        Schema::dropIfExists('purchase_lines');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('supplier_profiles');
    }
};

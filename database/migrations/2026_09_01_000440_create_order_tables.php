<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table): void {
            $table->ulid('id')->primary(); $table->string('document_number')->unique();
            $table->foreignUlid('customer_person_id')->constrained('people')->restrictOnDelete();
            $table->dateTime('ordered_at')->index(); $table->dateTime('due_at')->index();
            $table->string('priority')->default('normal')->index(); $table->string('status')->index();
            $table->foreignUlid('price_list_id')->constrained('price_lists')->restrictOnDelete();
            $table->unsignedBigInteger('subtotal'); $table->unsignedBigInteger('discount')->default(0); $table->unsignedBigInteger('total');
            $table->unsignedBigInteger('advance_amount')->default(0); $table->unsignedBigInteger('balance_amount');
            $table->text('notes')->nullable(); $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps(); $table->index(['customer_person_id','status','due_at']);
        });
        Schema::create('sales_order_lines', function (Blueprint $table): void {
            $table->ulid('id')->primary(); $table->foreignUlid('sales_order_id')->constrained('sales_orders')->cascadeOnDelete();
            $table->foreignUlid('presentation_id')->constrained('product_presentations')->restrictOnDelete();
            $table->foreignUlid('price_list_item_id')->nullable()->constrained('price_list_items')->nullOnDelete();
            $table->decimal('ordered_quantity',20,6); $table->decimal('reserved_quantity',20,6)->default(0); $table->decimal('produced_quantity',20,6)->default(0); $table->decimal('delivered_quantity',20,6)->default(0);
            $table->unsignedBigInteger('list_unit_price'); $table->unsignedBigInteger('unit_price'); $table->unsignedBigInteger('discount')->default(0); $table->unsignedBigInteger('line_total');
            $table->text('price_reason')->nullable(); $table->foreignUlid('price_authorization_id')->nullable()->constrained('authorization_requests')->restrictOnDelete(); $table->text('notes')->nullable(); $table->timestamps();
            $table->unique(['sales_order_id','presentation_id']);
        });
        Schema::create('inventory_reservations', function (Blueprint $table): void {
            $table->ulid('id')->primary(); $table->foreignUlid('presentation_id')->constrained('product_presentations')->restrictOnDelete(); $table->foreignUlid('sales_order_line_id')->constrained('sales_order_lines')->cascadeOnDelete();
            $table->decimal('quantity',20,6); $table->string('status')->index(); $table->dateTime('reserved_at'); $table->dateTime('released_at')->nullable(); $table->timestamps();
            $table->index(['presentation_id','status']);
        });
        Schema::create('order_status_histories', function (Blueprint $table): void {
            $table->ulid('id')->primary(); $table->foreignUlid('sales_order_id')->constrained('sales_orders')->cascadeOnDelete(); $table->string('from_status')->nullable(); $table->string('to_status'); $table->foreignId('changed_by')->constrained('users')->restrictOnDelete(); $table->dateTime('changed_at'); $table->text('notes')->nullable(); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('order_status_histories'); Schema::dropIfExists('inventory_reservations'); Schema::dropIfExists('sales_order_lines'); Schema::dropIfExists('sales_orders'); }
};

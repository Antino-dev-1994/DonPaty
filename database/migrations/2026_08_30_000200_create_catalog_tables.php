<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('dimension')->index();
            $table->decimal('scale_to_base', 20, 8);
            $table->unsignedTinyInteger('precision')->default(3);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('unit_conversions', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('from_unit_id')->constrained('units')->restrictOnDelete();
            $table->foreignUlid('to_unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('factor', 20, 8);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['from_unit_id', 'to_unit_id']);
        });

        Schema::create('items', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('code')->unique();
            $table->string('name')->index();
            $table->string('type')->index();
            $table->foreignUlid('base_unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('minimum_stock', 20, 6)->default(0);
            $table->boolean('allow_negative_stock')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('product_presentations', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('item_id')->constrained('items')->cascadeOnDelete();
            $table->string('sku')->unique();
            $table->string('name');
            $table->foreignUlid('stock_unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('conversion_to_item_base', 20, 8);
            $table->boolean('is_purchasable')->default(false);
            $table->boolean('is_sellable')->default(false);
            $table->boolean('is_stockable')->default(true);
            $table->boolean('is_active')->default(true)->index();
            $table->string('barcode')->nullable()->unique();
            $table->unsignedBigInteger('minimum_sale_price')->nullable();
            $table->timestamps();
            $table->index(['item_id', 'is_active']);
        });

        Schema::create('package_components', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('package_presentation_id')->constrained('product_presentations')->cascadeOnDelete();
            $table->foreignUlid('component_presentation_id')->constrained('product_presentations')->restrictOnDelete();
            $table->decimal('quantity', 20, 6);
            $table->timestamps();
            $table->unique(['package_presentation_id', 'component_presentation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_components');
        Schema::dropIfExists('product_presentations');
        Schema::dropIfExists('items');
        Schema::dropIfExists('unit_conversions');
        Schema::dropIfExists('units');
    }
};

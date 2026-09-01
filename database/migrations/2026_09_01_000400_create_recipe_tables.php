<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipes', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('code')->unique();
            $table->string('name')->index();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('recipe_versions', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('recipe_id')->constrained('recipes')->restrictOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('status')->index();
            $table->date('effective_from')->nullable()->index();
            $table->date('effective_to')->nullable()->index();
            $table->decimal('reference_flour_quantity', 20, 6);
            $table->foreignUlid('reference_flour_unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('expected_dough_yield', 20, 6);
            $table->foreignUlid('yield_unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('expected_waste_percentage', 8, 4)->default(0);
            $table->text('instructions')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('activated_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();
            $table->unique(['recipe_id', 'version_number']);
            $table->index(['recipe_id', 'status', 'effective_from']);
        });

        Schema::create('recipe_ingredients', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('recipe_version_id')->constrained('recipe_versions')->cascadeOnDelete();
            $table->foreignUlid('item_id')->constrained('items')->restrictOnDelete();
            $table->foreignUlid('presentation_id')->nullable()->constrained('product_presentations')->restrictOnDelete();
            $table->string('ingredient_role')->index();
            $table->decimal('quantity', 20, 6);
            $table->foreignUlid('unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('baker_percentage', 10, 4)->nullable();
            $table->boolean('allows_substitution')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['recipe_version_id', 'item_id', 'ingredient_role'], 'recipe_ingredient_unique');
        });

        Schema::create('recipe_compatible_products', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('recipe_version_id')->constrained('recipe_versions')->cascadeOnDelete();
            $table->foreignUlid('presentation_id')->constrained('product_presentations')->restrictOnDelete();
            $table->decimal('dough_weight_per_unit', 20, 6);
            $table->foreignUlid('dough_weight_unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('baking_loss_percentage', 8, 4)->default(0);
            $table->decimal('cost_weight_factor', 12, 6)->default(1);
            $table->timestamps();
            $table->unique(['recipe_version_id', 'presentation_id'], 'recipe_product_unique');
        });

        Schema::create('product_finishing_components', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('recipe_compatible_product_id')->constrained('recipe_compatible_products')->cascadeOnDelete();
            $table->foreignUlid('item_id')->constrained('items')->restrictOnDelete();
            $table->decimal('quantity_per_unit', 20, 6);
            $table->foreignUlid('unit_id')->constrained('units')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['recipe_compatible_product_id', 'item_id'], 'finishing_component_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_finishing_components');
        Schema::dropIfExists('recipe_compatible_products');
        Schema::dropIfExists('recipe_ingredients');
        Schema::dropIfExists('recipe_versions');
        Schema::dropIfExists('recipes');
    }
};

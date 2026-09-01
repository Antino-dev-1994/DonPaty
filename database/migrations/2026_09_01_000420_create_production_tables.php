<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cost_periods', function (Blueprint $table): void {
            $table->unsignedBigInteger('standard_labor_rate_per_kg')->default(0)->after('processed_flour_quantity');
            $table->text('labor_rate_reason')->nullable()->after('standard_labor_rate_per_kg');
        });
        Schema::table('cost_allocations', function (Blueprint $table): void {
            $table->timestamp('reversed_at')->nullable();
        });

        Schema::create('production_orders', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('document_number')->unique();
            $table->foreignUlid('recipe_version_id')->constrained('recipe_versions')->restrictOnDelete();
            $table->foreignUlid('cost_period_id')->constrained('cost_periods')->restrictOnDelete();
            $table->dateTime('planned_for')->index();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable()->index();
            $table->string('status')->index();
            $table->decimal('flour_quantity', 20, 6);
            $table->decimal('expected_dough_quantity', 20, 6);
            $table->decimal('actual_dough_quantity', 20, 6)->nullable();
            $table->decimal('waste_quantity', 20, 6)->nullable();
            $table->string('labor_method')->index();
            $table->foreignUlid('responsible_person_id')->constrained('people')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignUlid('negative_stock_authorization_id')->nullable()->constrained('authorization_requests')->restrictOnDelete();
            $table->foreignUlid('manual_labor_authorization_id')->nullable()->constrained('authorization_requests')->restrictOnDelete();
            $table->foreignUlid('consumption_movement_id')->nullable()->unique()->constrained('inventory_movements')->restrictOnDelete();
            $table->foreignUlid('output_movement_id')->nullable()->unique()->constrained('inventory_movements')->restrictOnDelete();
            $table->unsignedBigInteger('ingredient_cost')->default(0);
            $table->unsignedBigInteger('labor_cost')->default(0);
            $table->unsignedBigInteger('overhead_cost')->default(0);
            $table->unsignedBigInteger('total_cost')->default(0);
            $table->foreignId('reversed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('reversed_at')->nullable();
            $table->text('reversal_reason')->nullable();
            $table->foreignUlid('reversal_of_id')->nullable()->constrained('production_orders')->restrictOnDelete();
            $table->timestamps();
            $table->index(['recipe_version_id', 'status', 'planned_for']);
            $table->index(['cost_period_id', 'status']);
        });

        Schema::create('production_planned_outputs', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('production_order_id')->constrained('production_orders')->cascadeOnDelete();
            $table->foreignUlid('recipe_compatible_product_id')->constrained('recipe_compatible_products')->restrictOnDelete();
            $table->foreignUlid('presentation_id')->constrained('product_presentations')->restrictOnDelete();
            $table->decimal('planned_quantity', 20, 6);
            $table->decimal('planned_dough_quantity', 20, 6);
            $table->timestamps();
            $table->unique(['production_order_id', 'presentation_id']);
        });

        Schema::create('production_consumptions', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('production_order_id')->constrained('production_orders')->cascadeOnDelete();
            $table->foreignUlid('recipe_ingredient_id')->nullable()->constrained('recipe_ingredients')->restrictOnDelete();
            $table->foreignUlid('finishing_component_id')->nullable()->constrained('product_finishing_components')->restrictOnDelete();
            $table->foreignUlid('item_id')->constrained('items')->restrictOnDelete();
            $table->foreignUlid('presentation_id')->constrained('product_presentations')->restrictOnDelete();
            $table->decimal('calculated_quantity', 20, 6);
            $table->decimal('actual_quantity', 20, 6)->nullable();
            $table->foreignUlid('unit_id')->constrained('units')->restrictOnDelete();
            $table->unsignedBigInteger('unit_cost')->nullable();
            $table->unsignedBigInteger('total_cost')->nullable();
            $table->text('difference_reason')->nullable();
            $table->timestamps();
            $table->unique(['production_order_id', 'recipe_ingredient_id'], 'production_consumption_unique');
            $table->unique(['production_order_id', 'finishing_component_id'], 'production_finishing_consumption_unique');
        });

        Schema::create('production_outputs', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('production_order_id')->constrained('production_orders')->cascadeOnDelete();
            $table->foreignUlid('recipe_compatible_product_id')->constrained('recipe_compatible_products')->restrictOnDelete();
            $table->foreignUlid('presentation_id')->constrained('product_presentations')->restrictOnDelete();
            $table->decimal('quantity', 20, 6);
            $table->decimal('dough_quantity', 20, 6);
            $table->decimal('waste_quantity', 20, 6)->default(0);
            $table->unsignedBigInteger('allocated_cost');
            $table->unsignedBigInteger('unit_cost');
            $table->timestamps();
            $table->unique(['production_order_id', 'presentation_id']);
        });

        Schema::create('production_incidents', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('production_order_id')->constrained('production_orders')->cascadeOnDelete();
            $table->string('incident_type')->index();
            $table->text('description');
            $table->decimal('quantity', 20, 6)->nullable();
            $table->unsignedBigInteger('amount')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->dateTime('recorded_at');
            $table->timestamps();
        });

        Schema::create('production_labor_entries', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('production_order_id')->constrained('production_orders')->cascadeOnDelete();
            $table->foreignUlid('person_id')->nullable()->constrained('people')->restrictOnDelete();
            $table->string('method');
            $table->decimal('hours', 12, 4)->nullable();
            $table->unsignedBigInteger('hourly_rate')->nullable();
            $table->unsignedBigInteger('flour_rate')->nullable();
            $table->unsignedBigInteger('manual_amount')->nullable();
            $table->unsignedBigInteger('total_amount');
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('production_overhead_allocations', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('production_order_id')->constrained('production_orders')->cascadeOnDelete();
            $table->foreignUlid('overhead_rate_id')->constrained('overhead_rates')->restrictOnDelete();
            $table->string('cost_type');
            $table->decimal('base_quantity', 20, 6);
            $table->unsignedBigInteger('rate');
            $table->unsignedBigInteger('amount');
            $table->timestamps();
            $table->unique(['production_order_id', 'cost_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_overhead_allocations');
        Schema::dropIfExists('production_labor_entries');
        Schema::dropIfExists('production_incidents');
        Schema::dropIfExists('production_outputs');
        Schema::dropIfExists('production_consumptions');
        Schema::dropIfExists('production_planned_outputs');
        Schema::dropIfExists('production_orders');
        Schema::table('cost_allocations', function (Blueprint $table): void {
            $table->dropColumn('reversed_at');
        });
        Schema::table('cost_periods', function (Blueprint $table): void {
            $table->dropColumn(['standard_labor_rate_per_kg', 'labor_rate_reason']);
        });
    }
};

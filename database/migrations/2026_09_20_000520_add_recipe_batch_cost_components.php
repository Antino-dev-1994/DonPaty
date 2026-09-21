<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipe_batch_components', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('recipe_version_id')->constrained('recipe_versions')->cascadeOnDelete();
            $table->string('type')->index();
            $table->string('label');
            $table->foreignUlid('item_id')->nullable()->constrained('items')->restrictOnDelete();
            $table->decimal('quantity_per_batch', 20, 6)->nullable();
            $table->foreignUlid('unit_id')->nullable()->constrained('units')->restrictOnDelete();
            $table->unsignedBigInteger('amount_per_batch')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['recipe_version_id', 'sort_order']);
        });

        Schema::table('production_consumptions', function (Blueprint $table): void {
            $table->foreignUlid('recipe_batch_component_id')->nullable()->after('finishing_component_id')->constrained('recipe_batch_components')->restrictOnDelete();
            $table->unique(['production_order_id', 'recipe_batch_component_id'], 'production_batch_consumption_unique');
        });

        Schema::create('production_batch_costs', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('production_order_id')->constrained('production_orders')->cascadeOnDelete();
            $table->foreignUlid('recipe_batch_component_id')->constrained('recipe_batch_components')->restrictOnDelete();
            $table->string('label');
            $table->unsignedBigInteger('amount');
            $table->timestamps();
            $table->unique(['production_order_id', 'recipe_batch_component_id']);
        });

        Schema::table('production_orders', function (Blueprint $table): void {
            $table->unsignedBigInteger('batch_cost')->default(0)->after('overhead_cost');
        });
    }

    public function down(): void
    {
        Schema::table('production_orders', function (Blueprint $table): void { $table->dropColumn('batch_cost'); });
        Schema::dropIfExists('production_batch_costs');
        Schema::table('production_consumptions', function (Blueprint $table): void {
            $table->dropUnique('production_batch_consumption_unique');
            $table->dropConstrainedForeignId('recipe_batch_component_id');
        });
        Schema::dropIfExists('recipe_batch_components');
    }
};

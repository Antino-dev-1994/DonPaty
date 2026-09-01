<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_lists', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('name')->unique();
            $table->string('type')->index();
            $table->dateTime('starts_at')->nullable()->index();
            $table->dateTime('ends_at')->nullable()->index();
            $table->boolean('is_default')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('price_list_items', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('price_list_id')->constrained('price_lists')->cascadeOnDelete();
            $table->foreignUlid('presentation_id')->constrained('product_presentations')->restrictOnDelete();
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('minimum_price')->nullable();
            $table->timestamps();
            $table->unique(['price_list_id', 'presentation_id']);
        });

        Schema::create('customer_profiles', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('person_id')->unique()->constrained('people')->cascadeOnDelete();
            $table->foreignUlid('default_price_list_id')->nullable()->constrained('price_lists')->nullOnDelete();
            $table->unsignedBigInteger('credit_limit')->default(0);
            $table->unsignedInteger('default_payment_term_days')->default(0);
            $table->text('delivery_notes')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_profiles');
        Schema::dropIfExists('price_list_items');
        Schema::dropIfExists('price_lists');
    }
};

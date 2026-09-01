<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_categories', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('record_type')->index();
            $table->string('scope')->index();
            $table->foreignUlid('ledger_account_id')->constrained('financial_accounts')->restrictOnDelete();
            $table->foreignUlid('parent_id')->nullable()->constrained('financial_categories')->restrictOnDelete();
            $table->string('cost_type')->nullable()->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('income_records', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('document_number')->unique();
            $table->foreignUlid('financial_category_id')->constrained('financial_categories')->restrictOnDelete();
            $table->foreignUlid('person_id')->nullable()->constrained('people')->restrictOnDelete();
            $table->dateTime('effective_at')->index();
            $table->date('due_at')->nullable()->index();
            $table->string('description');
            $table->unsignedBigInteger('total_amount');
            $table->unsignedBigInteger('paid_amount')->default(0);
            $table->unsignedBigInteger('balance_amount');
            $table->string('status')->index();
            $table->foreignUlid('journal_entry_id')->nullable()->unique()->constrained('journal_entries')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->index(['financial_category_id', 'effective_at']);
        });

        Schema::create('expense_records', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('document_number')->unique();
            $table->foreignUlid('financial_category_id')->constrained('financial_categories')->restrictOnDelete();
            $table->foreignUlid('person_id')->nullable()->constrained('people')->restrictOnDelete();
            $table->foreignUlid('cost_period_id')->nullable()->constrained('cost_periods')->restrictOnDelete();
            $table->foreignUlid('cost_pool_entry_id')->nullable()->unique()->constrained('cost_pool_entries')->restrictOnDelete();
            $table->dateTime('effective_at')->index();
            $table->date('due_at')->nullable()->index();
            $table->string('description');
            $table->unsignedBigInteger('total_amount');
            $table->unsignedBigInteger('paid_amount')->default(0);
            $table->unsignedBigInteger('balance_amount');
            $table->string('status')->index();
            $table->foreignUlid('journal_entry_id')->nullable()->unique()->constrained('journal_entries')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->index(['financial_category_id', 'effective_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_records');
        Schema::dropIfExists('income_records');
        Schema::dropIfExists('financial_categories');
    }
};

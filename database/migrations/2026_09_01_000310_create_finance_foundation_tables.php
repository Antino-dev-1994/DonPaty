<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_accounts', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('account_type')->index();
            $table->string('scope')->index();
            $table->foreignUlid('person_id')->nullable()->constrained('people')->restrictOnDelete();
            $table->foreignUlid('parent_id')->nullable()->constrained('financial_accounts')->restrictOnDelete();
            $table->boolean('accepts_payments')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('payables', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('document_number')->unique();
            $table->foreignUlid('person_id')->constrained('people')->restrictOnDelete();
            $table->morphs('source');
            $table->date('issued_at')->index();
            $table->date('due_at')->nullable()->index();
            $table->unsignedBigInteger('original_amount');
            $table->unsignedBigInteger('paid_amount')->default(0);
            $table->unsignedBigInteger('credited_amount')->default(0);
            $table->unsignedBigInteger('balance_amount');
            $table->string('status')->index();
            $table->timestamps();
            $table->index(['person_id', 'status', 'due_at']);
            $table->unique(['source_type', 'source_id']);
        });

        Schema::create('payments', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('document_number')->unique();
            $table->string('direction')->index();
            $table->foreignUlid('person_id')->nullable()->constrained('people')->restrictOnDelete();
            $table->dateTime('paid_at')->index();
            $table->unsignedBigInteger('amount');
            $table->foreignUlid('financial_account_id')->constrained('financial_accounts')->restrictOnDelete();
            $table->string('status')->index();
            $table->string('reference')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('payment_allocations', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->morphs('allocatable');
            $table->unsignedBigInteger('amount');
            $table->timestamps();
            $table->unique(['payment_id', 'allocatable_type', 'allocatable_id'], 'payment_allocations_unique');
        });

        Schema::create('journal_entries', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('document_number')->unique();
            $table->dateTime('effective_at')->index();
            $table->string('description');
            $table->string('status')->index();
            $table->nullableMorphs('source');
            $table->foreignId('posted_by')->constrained('users')->restrictOnDelete();
            $table->foreignUlid('reversal_of_id')->nullable()->constrained('journal_entries')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('journal_lines', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();
            $table->foreignUlid('financial_account_id')->constrained('financial_accounts')->restrictOnDelete();
            $table->unsignedBigInteger('debit_amount')->default(0);
            $table->unsignedBigInteger('credit_amount')->default(0);
            $table->foreignUlid('person_id')->nullable()->constrained('people')->restrictOnDelete();
            $table->string('description')->nullable();
            $table->timestamps();
            $table->index(['financial_account_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('payment_allocations');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payables');
        Schema::dropIfExists('financial_accounts');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('household_transactions', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('document_number')->unique();
            $table->string('transaction_type')->index();
            $table->foreignUlid('person_id')->nullable()->constrained('people')->restrictOnDelete();
            $table->foreignUlid('financial_category_id')->nullable()->constrained('financial_categories')->restrictOnDelete();
            $table->foreignUlid('from_account_id')->nullable()->constrained('financial_accounts')->restrictOnDelete();
            $table->foreignUlid('to_account_id')->nullable()->constrained('financial_accounts')->restrictOnDelete();
            $table->dateTime('occurred_at')->index();
            $table->unsignedBigInteger('amount');
            $table->string('description');
            $table->boolean('counts_for_budget')->default(true)->index();
            $table->foreignUlid('journal_entry_id')->unique()->constrained('journal_entries')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->index(['person_id', 'occurred_at']);
        });

        Schema::create('fund_requests', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('document_number')->unique();
            $table->foreignUlid('requester_person_id')->constrained('people')->restrictOnDelete();
            $table->string('source_scope')->index();
            $table->unsignedBigInteger('amount');
            $table->text('reason');
            $table->date('needed_at')->index();
            $table->string('status')->index();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->dateTime('rejected_at')->nullable();
            $table->text('decision_notes')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->dateTime('paid_at')->nullable();
            $table->foreignUlid('source_account_id')->nullable()->constrained('financial_accounts')->restrictOnDelete();
            $table->foreignUlid('destination_account_id')->nullable()->constrained('financial_accounts')->restrictOnDelete();
            $table->foreignUlid('business_expense_record_id')->nullable()->constrained('expense_records')->restrictOnDelete();
            $table->foreignUlid('household_transaction_id')->nullable()->constrained('household_transactions')->restrictOnDelete();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->dateTime('confirmed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('fund_request_histories', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('fund_request_id')->constrained('fund_requests')->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignId('changed_by')->constrained('users')->restrictOnDelete();
            $table->dateTime('changed_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('household_budgets', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->string('status')->index();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->dateTime('confirmed_at')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['year', 'month']);
        });

        Schema::create('household_budget_lines', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('household_budget_id')->constrained('household_budgets')->cascadeOnDelete();
            $table->foreignUlid('financial_category_id')->constrained('financial_categories')->restrictOnDelete();
            $table->foreignUlid('person_id')->nullable()->constrained('people')->restrictOnDelete();
            $table->unsignedBigInteger('budgeted_amount');
            $table->timestamps();
            $table->index(['household_budget_id', 'financial_category_id', 'person_id'], 'household_budget_line_lookup');
        });

        Schema::create('debts', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('document_number')->unique();
            $table->foreignUlid('person_id')->nullable()->constrained('people')->restrictOnDelete();
            $table->string('direction')->index();
            $table->string('description');
            $table->unsignedBigInteger('principal_amount');
            $table->decimal('annual_interest_rate', 9, 6)->default(0);
            $table->date('start_date');
            $table->foreignUlid('financial_account_id')->constrained('financial_accounts')->restrictOnDelete();
            $table->string('status')->index();
            $table->unsignedBigInteger('principal_paid')->default(0);
            $table->unsignedBigInteger('interest_paid')->default(0);
            $table->foreignUlid('opening_journal_entry_id')->unique()->constrained('journal_entries')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('debt_installments', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('debt_id')->constrained('debts')->cascadeOnDelete();
            $table->unsignedSmallInteger('sequence');
            $table->date('due_at')->index();
            $table->unsignedBigInteger('principal_amount');
            $table->unsignedBigInteger('interest_amount');
            $table->unsignedBigInteger('principal_paid')->default(0);
            $table->unsignedBigInteger('interest_paid')->default(0);
            $table->string('status')->index();
            $table->timestamps();
            $table->unique(['debt_id', 'sequence']);
        });

        Schema::create('debt_payments', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('document_number')->unique();
            $table->foreignUlid('debt_id')->constrained('debts')->restrictOnDelete();
            $table->dateTime('paid_at')->index();
            $table->unsignedBigInteger('amount');
            $table->unsignedBigInteger('principal_amount');
            $table->unsignedBigInteger('interest_amount');
            $table->foreignUlid('financial_account_id')->constrained('financial_accounts')->restrictOnDelete();
            $table->foreignUlid('payment_id')->constrained('payments')->restrictOnDelete();
            $table->foreignUlid('journal_entry_id')->unique()->constrained('journal_entries')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('debt_payment_applications', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('debt_payment_id')->constrained('debt_payments')->cascadeOnDelete();
            $table->foreignUlid('debt_installment_id')->constrained('debt_installments')->restrictOnDelete();
            $table->unsignedBigInteger('principal_amount');
            $table->unsignedBigInteger('interest_amount');
            $table->timestamps();
            $table->unique(['debt_payment_id', 'debt_installment_id'], 'debt_payment_application_unique');
        });

        Schema::create('savings_goals', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->foreignUlid('person_id')->nullable()->constrained('people')->restrictOnDelete();
            $table->foreignUlid('financial_account_id')->constrained('financial_accounts')->restrictOnDelete();
            $table->unsignedBigInteger('target_amount');
            $table->date('target_date')->nullable();
            $table->string('status')->index();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('savings_contributions', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('savings_goal_id')->constrained('savings_goals')->cascadeOnDelete();
            $table->foreignUlid('source_account_id')->constrained('financial_accounts')->restrictOnDelete();
            $table->unsignedBigInteger('amount');
            $table->dateTime('contributed_at')->index();
            $table->foreignUlid('journal_entry_id')->unique()->constrained('journal_entries')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_contributions');
        Schema::dropIfExists('savings_goals');
        Schema::dropIfExists('debt_payment_applications');
        Schema::dropIfExists('debt_payments');
        Schema::dropIfExists('debt_installments');
        Schema::dropIfExists('debts');
        Schema::dropIfExists('household_budget_lines');
        Schema::dropIfExists('household_budgets');
        Schema::dropIfExists('fund_request_histories');
        Schema::dropIfExists('fund_requests');
        Schema::dropIfExists('household_transactions');
    }
};

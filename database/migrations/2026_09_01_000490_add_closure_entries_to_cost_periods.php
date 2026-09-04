<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cost_periods', function (Blueprint $table): void {
            $table->foreignUlid('closure_journal_entry_id')->nullable()->unique()->constrained('journal_entries')->restrictOnDelete();
            $table->foreignUlid('reopening_journal_entry_id')->nullable()->unique()->constrained('journal_entries')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cost_periods', function (Blueprint $table): void {
            $table->dropUnique(['reopening_journal_entry_id']);
            $table->dropUnique(['closure_journal_entry_id']);
            $table->dropConstrainedForeignId('reopening_journal_entry_id');
            $table->dropConstrainedForeignId('closure_journal_entry_id');
        });
    }
};

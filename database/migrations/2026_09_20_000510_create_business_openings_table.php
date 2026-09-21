<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_openings', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->date('opened_on')->unique();
            $table->unsignedBigInteger('cash_amount')->default(0);
            $table->unsignedBigInteger('nequi_amount')->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('opened_by')->constrained('users')->restrictOnDelete();
            $table->foreignUlid('journal_entry_id')->nullable()->unique()->constrained('journal_entries')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_openings');
    }
};

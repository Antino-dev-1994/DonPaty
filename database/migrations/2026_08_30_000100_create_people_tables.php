<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('document_type')->nullable();
            $table->string('document_number')->nullable()->unique();
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable()->index();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('person_classifications', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('person_id')->constrained('people')->cascadeOnDelete();
            $table->string('classification');
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->timestamps();

            $table->index(['person_id', 'classification']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('person_classifications');
        Schema::dropIfExists('people');
    }
};

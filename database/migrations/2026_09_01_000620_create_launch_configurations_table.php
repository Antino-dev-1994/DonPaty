<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('launch_configurations', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('status', 20)->default('draft')->index();
            $table->dateTime('cutoff_at')->nullable();
            $table->json('attestations')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->foreignId('activated_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('launch_configurations');
    }
};

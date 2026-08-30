<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authorization_requests', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('operation_type')->index();
            $table->string('approval_permission');
            $table->nullableMorphs('resource');
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->text('reason');
            $table->text('decision_notes')->nullable();
            $table->string('status')->default('pending')->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('decided_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->index(['resource_type', 'resource_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authorization_requests');
    }
};

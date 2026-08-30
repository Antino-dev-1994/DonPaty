<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignUlid('person_id')->nullable()->unique()->after('id')->constrained('people')->nullOnDelete();
            $table->string('status')->default('active')->after('password')->index();
            $table->timestamp('last_login_at')->nullable()->after('status');
            $table->timestamp('blocked_at')->nullable()->after('last_login_at');
            $table->text('blocked_reason')->nullable()->after('blocked_at');
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('name')->unique();
            $table->string('label');
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('name')->unique();
            $table->string('label');
            $table->string('group');
            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table): void {
            $table->foreignUlid('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->foreignUlid('role_id')->constrained('roles')->cascadeOnDelete();
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('role_user', function (Blueprint $table): void {
            $table->foreignUlid('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->primary(['role_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('person_id');
            $table->dropColumn(['status', 'last_login_at', 'blocked_at', 'blocked_reason']);
        });
    }
};

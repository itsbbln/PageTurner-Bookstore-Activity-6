<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scheduled_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('task_name', 120)->index();
            $table->enum('status', ['success', 'failed'])->index();
            $table->text('message')->nullable();
            $table->timestamp('executed_at')->useCurrent()->index();
            $table->timestamps();
        });

        Schema::create('api_rate_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address', 45)->nullable()->index();
            $table->string('endpoint', 255)->nullable();
            $table->string('scope', 80)->nullable();
            $table->unsignedInteger('limit_value')->nullable();
            $table->unsignedInteger('remaining')->nullable();
            $table->unsignedInteger('retry_after')->nullable();
            $table->timestamp('hit_at')->useCurrent()->index();
            $table->timestamps();
        });

        Schema::create('backup_monitoring', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['success', 'failed'])->index();
            $table->string('operation', 80)->default('backup:run')->index();
            $table->string('storage_disk', 80)->nullable();
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->text('message')->nullable();
            $table->timestamp('executed_at')->useCurrent()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backup_monitoring');
        Schema::dropIfExists('api_rate_limits');
        Schema::dropIfExists('scheduled_tasks');
    }
};

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
        Schema::create('export_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('type', 50)->default('books'); // books|orders|users|audit
            $table->string('format', 10)->default('xlsx'); // xlsx|csv|pdf|json
            $table->json('filters')->nullable();
            $table->json('columns')->nullable(); // selected export columns

            $table->enum('status', ['queued', 'running', 'completed', 'failed'])->default('queued');
            $table->unsignedBigInteger('record_count')->nullable();

            $table->string('stored_disk', 50)->default('local');
            $table->string('stored_path')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('export_logs');
    }
};

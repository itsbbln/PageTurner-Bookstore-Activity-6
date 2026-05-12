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
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('type', 50)->default('books'); // books|users|...
            $table->string('original_filename');
            $table->string('stored_path')->nullable();
            $table->string('stored_disk', 50)->default('local');
            $table->string('mime_type')->nullable();

            $table->boolean('update_existing')->default(false);
            $table->enum('status', ['queued', 'running', 'completed', 'completed_with_errors', 'failed'])->default('queued');

            $table->unsignedInteger('total_rows')->nullable();
            $table->unsignedInteger('processed_rows')->default(0);
            $table->unsignedInteger('successful_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);

            // Store a limited set of failures for UI + downloadable report
            $table->json('failures')->nullable();
            $table->text('failure_report_path')->nullable();

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
        Schema::dropIfExists('import_logs');
    }
};

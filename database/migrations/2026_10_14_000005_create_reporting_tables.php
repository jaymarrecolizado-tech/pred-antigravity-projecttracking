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
        // 1. Interventions (Append-only state change and event log)
        Schema::create('interventions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_site_id')->constrained('project_sites')->cascadeOnDelete();
            $table->string('intervention_type', 64);
            $table->string('previous_status', 64)->nullable();
            $table->string('new_status', 64);
            $table->timestamp('occurred_at')->useCurrent();
            $table->foreignId('created_by')->constrained('users');
            $table->enum('source', ['web_form', 'excel_upload', 'api_sync'])->default('web_form');
            $table->timestamps();

            $table->index(['project_site_id', 'occurred_at']);
        });

        // 2. Field Reports (Telemetry data, GPS coordinates, notes, custom field values)
        Schema::create('field_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intervention_id')->constrained('interventions')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->decimal('submission_lat', 9, 6)->nullable();
            $table->decimal('submission_lng', 9, 6)->nullable();
            $table->float('gps_accuracy_m')->nullable();
            $table->timestamp('device_timestamp')->nullable();
            $table->timestamp('server_timestamp')->useCurrent();
            $table->json('custom_values')->nullable(); // Matches field_defs
            $table->enum('verification_status', ['pending', 'verified', 'flagged', 'rejected'])->default('pending');
            $table->json('verification_flags')->nullable(); // e.g. ["OUTSIDE_SITE_PROXIMITY", "DUPLICATE_IMAGE_HASH"]
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();

            $table->index(['verification_status', 'created_at']);
        });

        // 3. Attachments (Photo evidence, speed tests, signed acceptance documents)
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_report_id')->constrained('field_reports')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('thumbnail_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('mime_type', 64);
            $table->unsignedBigInteger('file_size_bytes');
            $table->char('sha256_hash', 64)->index();
            $table->decimal('exif_lat', 9, 6)->nullable();
            $table->decimal('exif_lng', 9, 6)->nullable();
            $table->timestamp('exif_timestamp')->nullable();
            $table->timestamps();
        });

        // 4. Correction Requests (Audit workflow for amendments)
        Schema::create('correction_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_report_id')->constrained('field_reports')->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->json('proposed_diff');
            $table->text('review_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('correction_requests');
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('field_reports');
        Schema::dropIfExists('interventions');
    }
};

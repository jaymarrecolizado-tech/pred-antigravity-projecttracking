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
        // 1. Central Locations Registry (Physical coordinates and geographic anchor)
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category', 32)->default('other'); // lgu_hall, school, plaza, rhu, public_market, terminal, other
            $table->char('psgc_barangay', 10)->index();
            $table->decimal('lat', 9, 6);
            $table->decimal('lng', 9, 6);
            $table->float('elevation_m')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->text('address_details')->nullable();
            $table->timestamps();

            $table->index(['lat', 'lng']);
        });

        // 2. Central Institutions Registry (LGUs, DepEd Schools, SUCs, Hospitals)
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->enum('kind', ['lgu', 'deped_school', 'suc', 'hospital_clinic', 'nga_regional_office', 'other'])->default('other');
            $table->string('name');
            $table->string('external_id', 64)->nullable()->index(); // DepEd School ID, LGU Code, etc.
            $table->char('psgc_code', 10)->index();
            $table->foreignId('primary_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('contact_person')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->timestamps();
        });

        // 3. Project Sites (Enrollment of Location/Institution into a Project)
        Schema::create('project_sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('institution_id')->nullable()->constrained('institutions')->nullOnDelete();
            $table->string('site_code', 64)->nullable()->index();
            $table->string('current_status', 64);
            $table->foreignId('assigned_encoder_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('target_completion_date')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamps();

            $table->index(['project_id', 'current_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_sites');
        Schema::dropIfExists('institutions');
        Schema::dropIfExists('locations');
    }
};

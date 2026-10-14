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
        // 1. Programs (Umbrella groupings, e.g. Free Wi-Fi, National Broadband Program, e-Government)
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('owner_unit')->nullable(); // NIPPSB, IIDB, ILCDB, etc.
            $table->string('color', 9)->default('#1d4ed8');
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Project Types (Templates defining tracking archetype Kind A, B, C, D)
        Schema::create('project_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('kind', ['site', 'institution', 'activity', 'milestone'])->default('site');
            $table->string('marker_shape', 32)->default('circle'); // circle, square, diamond, triangle, pin
            $table->string('color_code', 9)->default('#2563eb');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 3. Status Definitions (Configurable workflow statuses per Project Type)
        Schema::create('status_defs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_type_id')->constrained('project_types')->cascadeOnDelete();
            $table->string('key', 64);
            $table->string('label');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_completed')->default(false); // Counts toward completion %
            $table->boolean('is_terminal')->default(false);
            $table->json('next_keys')->nullable(); // JSON array of valid next status keys
            $table->timestamps();

            $table->unique(['project_type_id', 'key']);
        });

        // 4. Intervention Types (Append-only logged events, e.g. installation, inspection, orientation)
        Schema::create('intervention_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_type_id')->constrained('project_types')->cascadeOnDelete();
            $table->string('key', 64);
            $table->string('label');
            $table->timestamps();

            $table->unique(['project_type_id', 'key']);
        });

        // 5. Custom Field Definitions (Dynamic form builder per Project Type)
        Schema::create('field_defs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_type_id')->constrained('project_types')->cascadeOnDelete();
            $table->string('key', 64);
            $table->string('label');
            $table->enum('type', [
                'text',
                'number',
                'integer',
                'select',
                'multiselect',
                'date',
                'boolean',
                'sex_disaggregated'
            ])->default('text');
            $table->json('options')->nullable(); // For select / multiselect options
            $table->boolean('required')->default(false);
            $table->enum('applies_to', ['site', 'report', 'activity'])->default('report');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['project_type_id', 'key']);
        });

        // 6. Projects (Concrete initiatives, e.g. Free Wi-Fi 2026 Phase 2, eLGU Rollout R2)
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('name');
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->foreignId('project_type_id')->constrained('project_types');
            $table->enum('lifecycle', ['draft', 'active', 'closed', 'archived'])->default('draft');
            $table->decimal('budget', 15, 2)->nullable();
            $table->string('funding_source')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('color', 9)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 7. Project Scopes (Geographic scope: targeted provinces or municipalities)
        Schema::create('project_scopes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->char('psgc_code', 10)->index();
            $table->timestamps();

            $table->unique(['project_id', 'psgc_code']);
        });

        // 8. Indicator Targets (Numeric targets per indicator, area, and period)
        Schema::create('indicator_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('indicator_key', 64);
            $table->char('psgc_code', 10)->nullable()->index(); // Null = regional target
            $table->string('period', 16); // e.g. '2026', '2026-Q1'
            $table->decimal('target_value', 14, 2);
            $table->timestamps();

            $table->unique(['project_id', 'indicator_key', 'psgc_code', 'period'], 'uniq_proj_target');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indicator_targets');
        Schema::dropIfExists('project_scopes');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('field_defs');
        Schema::dropIfExists('intervention_types');
        Schema::dropIfExists('status_defs');
        Schema::dropIfExists('project_types');
        Schema::dropIfExists('programs');
    }
};

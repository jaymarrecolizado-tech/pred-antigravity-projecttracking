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
        Schema::create('rollup_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->char('psgc_code', 10)->index();
            $table->date('date')->index();
            $table->integer('total_sites')->default(0);
            $table->integer('completed_sites')->default(0);
            $table->integer('in_progress_sites')->default(0);
            $table->integer('backlog_sites')->default(0);
            $table->integer('operational_sites')->default(0);
            $table->decimal('indicator_actual', 14, 2)->default(0);
            $table->timestamps();

            $table->unique(['project_id', 'psgc_code', 'date'], 'rollup_proj_psgc_date_unique');
            $table->index(['psgc_code', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rollup_daily');
    }
};

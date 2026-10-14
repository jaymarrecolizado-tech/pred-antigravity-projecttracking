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
        Schema::table('users', function (Blueprint $table) {
            $table->string('agency_unit')->nullable()->after('email');
            $table->string('phone', 32)->nullable()->after('agency_unit');
            $table->boolean('is_active')->default(true)->after('phone');
        });

        Schema::create('role_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', [
                'super_admin',
                'project_manager',
                'reviewer',
                'encoder',
                'partner',
                'executive_viewer'
            ]);
            $table->unsignedBigInteger('project_id')->nullable()->index(); // Nullable: applies to all projects
            $table->unsignedBigInteger('program_id')->nullable()->index(); // Nullable: applies across whole program
            $table->char('psgc_code', 10)->nullable()->index();            // Nullable: null = whole region (e.g. '02' or '0231')
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_grants');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['agency_unit', 'phone', 'is_active']);
        });
    }
};

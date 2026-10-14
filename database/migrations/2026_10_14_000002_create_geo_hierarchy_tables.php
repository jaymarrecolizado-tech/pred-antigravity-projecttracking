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
        // 1. Philippine Standard Geographic Code (PSGC) areas
        Schema::create('psgc_areas', function (Blueprint $table) {
            $table->char('code', 10)->primary(); // e.g. '0200000000', '0231000000'
            $table->string('name');
            $table->enum('level', ['reg', 'prov', 'city', 'mun', 'submun', 'bgy'])->index();
            $table->char('region_code', 10)->index();
            $table->char('province_code', 10)->nullable()->index();
            $table->char('citymun_code', 10)->nullable()->index();
            $table->char('parent_code', 10)->nullable()->index();
            $table->string('city_class', 32)->nullable(); // HUC, ICC, CC
            $table->decimal('centroid_lat', 9, 6)->nullable();
            $table->decimal('centroid_lng', 9, 6)->nullable();
            $table->string('psgc_release', 16)->default('2026Q2');
            $table->timestamps();
        });

        // 2. Congressional Districts (Versioned, separate from PSGC tree)
        Schema::create('congressional_districts', function (Blueprint $table) {
            $table->id();
            $table->char('province_code', 10)->index(); // Foreign link to psgc_areas
            $table->string('name'); // e.g. "Cagayan 3rd District", "Isabela 1st District", "Batanes Lone District"
            $table->unsignedTinyInteger('district_number')->nullable(); // null for Lone Districts
            $table->timestamps();
        });

        // 3. District Memberships (Reapportionment-safe history)
        Schema::create('district_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained('congressional_districts')->cascadeOnDelete();
            $table->char('citymun_code', 10)->index();
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();

            $table->unique(['citymun_code', 'effective_from'], 'uniq_citymun_effective');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('district_memberships');
        Schema::dropIfExists('congressional_districts');
        Schema::dropIfExists('psgc_areas');
    }
};

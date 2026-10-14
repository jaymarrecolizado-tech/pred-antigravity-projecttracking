<?php

namespace Database\Seeders;

use App\Domain\Access\GrantResolver;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Regional Super Admin (2FA enabled)
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@dict.gov.ph'],
            [
                'name' => 'Regional Director / Super Admin',
                'password' => Hash::make('AdminSecret123!'),
                'agency_unit' => 'DICT Region 2 Executive Office',
                'phone' => '+63 917 100 0001',
                'is_active' => true,
                'two_factor_secret' => 'SECRET2FAKEYDEMO',
                'two_factor_recovery_codes' => json_encode(['REC-001', 'REC-002']),
                'two_factor_confirmed_at' => now(),
            ]
        );
        $superAdmin->roleGrants()->firstOrCreate([
            'role' => GrantResolver::ROLE_SUPER_ADMIN,
        ]);

        // 2. Project Manager (Cagayan Province scope, 2FA enabled)
        $pmCagayan = User::updateOrCreate(
            ['email' => 'pm.cagayan@dict.gov.ph'],
            [
                'name' => 'Cagayan Project Lead',
                'password' => Hash::make('Password123!'),
                'agency_unit' => 'DICT Cagayan Provincial Office',
                'phone' => '+63 917 200 0002',
                'is_active' => true,
                'two_factor_secret' => 'SECRET2FAKEYDEMO',
                'two_factor_recovery_codes' => json_encode(['REC-003', 'REC-004']),
                'two_factor_confirmed_at' => now(),
            ]
        );
        $pmCagayan->roleGrants()->firstOrCreate([
            'role' => GrantResolver::ROLE_PROJECT_MANAGER,
            'psgc_code' => '0215', // Cagayan Province
        ]);

        // 3. Regional Reviewer (Isabela Province scope, 2FA enabled)
        $reviewerIsabela = User::updateOrCreate(
            ['email' => 'reviewer.isabela@dict.gov.ph'],
            [
                'name' => 'Isabela Quality Reviewer',
                'password' => Hash::make('Password123!'),
                'agency_unit' => 'DICT Isabela Technical Operations',
                'phone' => '+63 917 300 0003',
                'is_active' => true,
                'two_factor_secret' => 'SECRET2FAKEYDEMO',
                'two_factor_recovery_codes' => json_encode(['REC-005', 'REC-006']),
                'two_factor_confirmed_at' => now(),
            ]
        );
        $reviewerIsabela->roleGrants()->firstOrCreate([
            'role' => GrantResolver::ROLE_REVIEWER,
            'psgc_code' => '0231', // Isabela Province
        ]);

        // 4. Field Encoder (Tuguegarao City scope, 2FA not mandated for encoders)
        $encoderTuguegarao = User::updateOrCreate(
            ['email' => 'encoder.tuguegarao@dict.gov.ph'],
            [
                'name' => 'Tuguegarao Field Officer',
                'password' => Hash::make('Password123!'),
                'agency_unit' => 'Field Operations - Tuguegarao Unit',
                'phone' => '+63 917 400 0004',
                'is_active' => true,
            ]
        );
        $encoderTuguegarao->roleGrants()->firstOrCreate([
            'role' => GrantResolver::ROLE_ENCODER,
            'psgc_code' => '021528', // Tuguegarao City
        ]);

        // 5. Unconfigured Privileged Admin (To test 2FA mandatory enforcement redirection)
        $unprotected = User::updateOrCreate(
            ['email' => 'unprotected.pm@dict.gov.ph'],
            [
                'name' => 'New Project Manager (Unconfigured 2FA)',
                'password' => Hash::make('Password123!'),
                'agency_unit' => 'DICT Region 2 Planning',
                'phone' => '+63 917 500 0005',
                'is_active' => true,
                // Notice: two_factor_confirmed_at is NULL
            ]
        );
        $unprotected->roleGrants()->firstOrCreate([
            'role' => GrantResolver::ROLE_PROJECT_MANAGER,
        ]);
    }
}

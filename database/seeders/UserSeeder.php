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
        // 1. Regional Super Admin (System-wide authority)
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@dict.gov.ph'],
            [
                'name' => 'Regional Director / Super Admin',
                'password' => Hash::make('Password123!'),
                'agency_unit' => 'DICT Region 2 Executive Office',
                'phone' => '+63 917 100 0001',
                'is_active' => true,
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
            ]
        );
        $superAdmin->roleGrants()->firstOrCreate([
            'role' => GrantResolver::ROLE_SUPER_ADMIN,
        ]);

        // 2. Project Manager (Regional / Free Wi-Fi lead)
        $pmNbp = User::updateOrCreate(
            ['email' => 'pm.nbp@dict.gov.ph'],
            [
                'name' => 'Free Wi-Fi & NBP Project Manager',
                'password' => Hash::make('Password123!'),
                'agency_unit' => 'DICT Region 2 Technical Operations',
                'phone' => '+63 917 200 0001',
                'is_active' => true,
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
            ]
        );
        $pmNbp->roleGrants()->firstOrCreate([
            'role' => GrantResolver::ROLE_PROJECT_MANAGER,
        ]);

        // 3. Cagayan Project Lead
        $pmCagayan = User::updateOrCreate(
            ['email' => 'pm.cagayan@dict.gov.ph'],
            [
                'name' => 'Cagayan Project Lead',
                'password' => Hash::make('Password123!'),
                'agency_unit' => 'DICT Cagayan Provincial Office',
                'phone' => '+63 917 200 0002',
                'is_active' => true,
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
            ]
        );
        $pmCagayan->roleGrants()->firstOrCreate([
            'role' => GrantResolver::ROLE_PROJECT_MANAGER,
            'psgc_code' => '0215', // Cagayan Province
        ]);

        // 4. Regional Reviewer (QA Queue)
        $reviewerR02 = User::updateOrCreate(
            ['email' => 'reviewer.r02@dict.gov.ph'],
            [
                'name' => 'Regional Quality Assurance Reviewer',
                'password' => Hash::make('Password123!'),
                'agency_unit' => 'DICT Region 2 Quality & Verification',
                'phone' => '+63 917 300 0001',
                'is_active' => true,
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
            ]
        );
        $reviewerR02->roleGrants()->firstOrCreate([
            'role' => GrantResolver::ROLE_REVIEWER,
        ]);

        // 5. Isabela Quality Reviewer
        $reviewerIsabela = User::updateOrCreate(
            ['email' => 'reviewer.isabela@dict.gov.ph'],
            [
                'name' => 'Isabela Quality Reviewer',
                'password' => Hash::make('Password123!'),
                'agency_unit' => 'DICT Isabela Technical Operations',
                'phone' => '+63 917 300 0003',
                'is_active' => true,
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
            ]
        );
        $reviewerIsabela->roleGrants()->firstOrCreate([
            'role' => GrantResolver::ROLE_REVIEWER,
            'psgc_code' => '0231', // Isabela Province
        ]);

        // 6. Field Encoder (Tuguegarao City / Cagayan, 2FA not mandated)
        $encoderTuguegarao = User::updateOrCreate(
            ['email' => 'encoder.tuguegarao@dict.gov.ph'],
            [
                'name' => 'Tuguegarao Field Officer',
                'password' => Hash::make('Password123!'),
                'agency_unit' => 'Field Operations - Tuguegarao Unit',
                'phone' => '+63 917 400 0004',
                'is_active' => true,
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
            ]
        );
        $encoderTuguegarao->roleGrants()->firstOrCreate([
            'role' => GrantResolver::ROLE_ENCODER,
            'psgc_code' => '021528', // Tuguegarao City
        ]);

        // 7. Partner / LGU Viewer (Isabela Province scope, read-only)
        $partnerIlagan = User::updateOrCreate(
            ['email' => 'partner.ilagan@dict.gov.ph'],
            [
                'name' => 'City of Ilagan LGU Focal',
                'password' => Hash::make('Password123!'),
                'agency_unit' => 'LGU City of Ilagan IT Office',
                'phone' => '+63 917 500 0001',
                'is_active' => true,
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
            ]
        );
        $partnerIlagan->roleGrants()->firstOrCreate([
            'role' => GrantResolver::ROLE_PARTNER,
            'psgc_code' => '023114', // City of Ilagan
        ]);

        // 8. Unconfigured Privileged Admin (To test 2FA mandatory enforcement redirection)
        $unprotected = User::updateOrCreate(
            ['email' => 'unprotected.pm@dict.gov.ph'],
            [
                'name' => 'New Project Manager (Unconfigured 2FA)',
                'password' => Hash::make('Password123!'),
                'agency_unit' => 'DICT Region 2 Planning',
                'phone' => '+63 917 500 0005',
                'is_active' => true,
            ]
        );
        $unprotected->roleGrants()->firstOrCreate([
            'role' => GrantResolver::ROLE_PROJECT_MANAGER,
        ]);
    }
}

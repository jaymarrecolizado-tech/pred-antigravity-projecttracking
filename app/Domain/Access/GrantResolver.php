<?php

namespace App\Domain\Access;

use App\Models\User;

class GrantResolver
{
    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_PROJECT_MANAGER = 'project_manager';
    public const ROLE_REVIEWER = 'reviewer';
    public const ROLE_ENCODER = 'encoder';
    public const ROLE_PARTNER = 'partner';
    public const ROLE_EXECUTIVE_VIEWER = 'executive_viewer';

    public const ABILITIES = [
        self::ROLE_SUPER_ADMIN => [
            '*',
        ],
        self::ROLE_PROJECT_MANAGER => [
            'map.view',
            'project.view',
            'project.create',
            'project.update',
            'site.enroll',
            'site.import',
            'site.assign',
            'target.manage',
            'report.view',
            'report.export',
        ],
        self::ROLE_REVIEWER => [
            'map.view',
            'project.view',
            'site.view',
            'submission.view',
            'submission.verify',
            'submission.flag',
            'correction.approve',
            'site.status_bulk',
            'report.view',
        ],
        self::ROLE_ENCODER => [
            'map.view',
            'project.view',
            'site.view',
            'submission.view',
            'submission.create',
            'correction.request',
        ],
        self::ROLE_PARTNER => [
            'map.view',
            'project.view',
            'site.view_assigned',
            'submission.create',
        ],
        self::ROLE_EXECUTIVE_VIEWER => [
            'dashboard.view',
            'map.view',
            'project.view',
            'report.view',
            'report.export',
        ],
    ];

    /**
     * Determines if a user has permission for an ability, optionally scoped by project and PSGC location.
     */
    public function allows(User $user, string $ability, ?int $projectId = null, ?int $programId = null, ?string $psgcCode = null): bool
    {
        if (!$user->is_active) {
            return false;
        }

        // Fetch user grants (eager loaded if possible)
        $grants = $user->relationLoaded('roleGrants') ? $user->roleGrants : $user->roleGrants()->get();

        foreach ($grants as $grant) {
            // Super Admin bypasses all checks
            if ($grant->role === self::ROLE_SUPER_ADMIN) {
                return true;
            }

            // Check if grant role includes ability
            $roleAbilities = self::ABILITIES[$grant->role] ?? [];
            if (!in_array($ability, $roleAbilities, true) && !in_array('*', $roleAbilities, true)) {
                continue;
            }

            // Check project scope
            if (!$grant->coversProject($projectId, $programId)) {
                continue;
            }

            // Check geographic PSGC scope
            if (!$grant->coversPsgc($psgcCode)) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * Returns true if user has a privileged role that requires mandatory 2FA.
     */
    public function requiresTwoFactor(User $user): bool
    {
        $privilegedRoles = [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_PROJECT_MANAGER,
            self::ROLE_REVIEWER,
        ];

        $grants = $user->relationLoaded('roleGrants') ? $user->roleGrants : $user->roleGrants()->get();

        return $grants->contains(fn ($grant) => in_array($grant->role, $privilegedRoles, true));
    }
}

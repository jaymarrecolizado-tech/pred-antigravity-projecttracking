<?php

namespace App\Domain\Access;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleGrant extends Model
{
    use HasFactory;

    protected $table = 'role_grants';

    protected $fillable = [
        'user_id',
        'role',
        'project_id',
        'program_id',
        'psgc_code',
        'granted_by',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function grantor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    /**
     * Checks if this grant covers a given PSGC code using hierarchical prefix matching.
     * E.g. Grant on Isabela '0231' covers Ilagan City '023114' and its barangays '023114001'.
     */
    public function coversPsgc(?string $targetPsgc): bool
    {
        if (empty($this->psgc_code)) {
            return true; // Null means unrestricted / region-wide
        }

        if (empty($targetPsgc)) {
            return true;
        }

        // Clean trailing zeros for prefix hierarchy (Region 02, Province 0231, City/Mun 023114)
        $grantPrefix = rtrim($this->psgc_code, '0');
        return str_starts_with($targetPsgc, $grantPrefix);
    }

    /**
     * Checks if this grant covers the given project and program.
     */
    public function coversProject(?int $projectId, ?int $programId = null): bool
    {
        // Global project grant
        if ($this->project_id === null && $this->program_id === null) {
            return true;
        }

        // Direct project match
        if ($this->project_id !== null && $this->project_id === $projectId) {
            return true;
        }

        // Program-level umbrella match
        if ($this->program_id !== null && $this->program_id === $programId) {
            return true;
        }

        return false;
    }
}

<?php

namespace App\Domain\Portfolio;

use InvalidArgumentException;

class StatusWorkflow
{
    /**
     * Get all allowed next status keys from a given current status.
     *
     * @param ProjectType $projectType
     * @param string|null $currentKey
     * @return array<string>
     */
    public function allowedNext(ProjectType $projectType, ?string $currentKey): array
    {
        $statusDefs = $projectType->statusDefs->keyBy('key');

        if ($currentKey === null || $currentKey === '') {
            $first = $projectType->statusDefs->sortBy('sort_order')->first();
            return $first ? [$first->key] : [];
        }

        /** @var StatusDef|null $current */
        $current = $statusDefs->get($currentKey);
        if (!$current) {
            return [];
        }

        if ($current->is_terminal) {
            return [];
        }

        // If next_keys is explicitly configured (even as empty array), respect it
        if ($current->next_keys !== null) {
            return array_values($current->next_keys);
        }

        // Default sequential fallback: next status by sort_order
        $next = $projectType->statusDefs
            ->where('sort_order', '>', $current->sort_order)
            ->sortBy('sort_order')
            ->first();

        return $next ? [$next->key] : [];
    }

    /**
     * Check if a transition from one status to another is valid.
     *
     * @param ProjectType $projectType
     * @param string|null $fromKey
     * @param string $toKey
     * @return bool
     */
    public function canTransition(ProjectType $projectType, ?string $fromKey, string $toKey): bool
    {
        $allowed = $this->allowedNext($projectType, $fromKey);
        return in_array($toKey, $allowed, true);
    }

    /**
     * Validate the entire workflow graph of a ProjectType.
     * Returns an array of error messages, or empty array if valid.
     *
     * @param ProjectType $projectType
     * @return array<string>
     */
    public function validateWorkflow(ProjectType $projectType): array
    {
        $errors = [];
        $statusDefs = $projectType->statusDefs;

        if ($statusDefs->isEmpty()) {
            $errors[] = "Project type '{$projectType->name}' has no defined statuses.";
            return $errors;
        }

        $allKeys = $statusDefs->pluck('key')->all();

        foreach ($statusDefs as $status) {
            if ($status->next_keys !== null) {
                foreach ($status->next_keys as $nextKey) {
                    if (!in_array($nextKey, $allKeys, true)) {
                        $errors[] = "Status '{$status->key}' references unknown next status '{$nextKey}'.";
                    }
                }
            }
        }

        $hasCompleted = $statusDefs->contains('is_completed', true);
        if (!$hasCompleted) {
            $errors[] = "Project type '{$projectType->name}' has no status marked as 'is_completed'.";
        }

        return $errors;
    }

    /**
     * Compute completion percentage based on current status and project type.
     *
     * @param ProjectType $projectType
     * @param string $statusKey
     * @return float
     */
    public function calculateProgress(ProjectType $projectType, string $statusKey): float
    {
        $sorted = $projectType->statusDefs->sortBy('sort_order')->values();
        $total = $sorted->count();
        if ($total === 0) {
            return 0.0;
        }

        $index = $sorted->search(fn (StatusDef $def) => $def->key === $statusKey);
        if ($index === false) {
            return 0.0;
        }

        /** @var StatusDef $current */
        $current = $sorted->get($index);
        if ($current->is_completed) {
            return 100.0;
        }

        // Percentage based on progress through non-terminal milestones
        return round((($index + 1) / $total) * 100.0, 1);
    }
}

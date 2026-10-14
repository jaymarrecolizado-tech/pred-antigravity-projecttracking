<?php

namespace App\Domain\Reporting;

use App\Domain\Geo\BoundaryValidator;
use App\Domain\Portfolio\StatusWorkflow;
use App\Domain\Registry\ProjectSite;

class FraudDetector
{
    public const FLAG_OUTSIDE_PROXIMITY = 'OUTSIDE_SITE_PROXIMITY';
    public const FLAG_DUPLICATE_IMAGE = 'DUPLICATE_IMAGE_HASH';
    public const FLAG_EXIF_MISMATCH = 'EXIF_GPS_MISMATCH';
    public const FLAG_ILLEGAL_TRANSITION = 'ILLEGAL_STATUS_TRANSITION';

    public function __construct(
        protected BoundaryValidator $boundaryValidator,
        protected StatusWorkflow $workflow
    ) {}

    /**
     * Run all anti-fraud checks on a proposed field report submission.
     *
     * @param ProjectSite $site
     * @param string $newStatus
     * @param float|null $submissionLat
     * @param float|null $submissionLng
     * @param array<string> $imageHashes Array of SHA-256 hashes of attached photos
     * @param array<array{lat: float|null, lng: float|null}> $exifCoords
     * @return array<string> Array of verification flag codes
     */
    public function evaluate(
        ProjectSite $site,
        string $newStatus,
        ?float $submissionLat = null,
        ?float $submissionLng = null,
        array $imageHashes = [],
        array $exifCoords = []
    ): array {
        $flags = [];

        // 1. Status Transition Validation
        $projectType = $site->project?->projectType;
        if ($projectType) {
            $allowed = $this->workflow->allowedNext($projectType, $site->current_status);
            // If current status is same as new status (e.g. routine inspection visit without status change), that is allowed
            if ($newStatus !== $site->current_status && !in_array($newStatus, $allowed, true)) {
                $flags[] = self::FLAG_ILLEGAL_TRANSITION;
            }
        }

        // 2. GPS Proximity Verification (< 500 meters from registered site location)
        $siteCoords = $site->getCoordinates();
        if ($siteCoords && $submissionLat !== null && $submissionLng !== null) {
            $dist = $this->boundaryValidator->haversineDistanceMeters(
                $submissionLat,
                $submissionLng,
                $siteCoords['lat'],
                $siteCoords['lng']
            );

            if ($dist > 500.0) {
                $flags[] = self::FLAG_OUTSIDE_PROXIMITY;
            }
        }

        // 3. Duplicate Image SHA-256 Hash Detection
        foreach ($imageHashes as $hash) {
            if ($hash && Attachment::where('sha256_hash', $hash)->exists()) {
                $flags[] = self::FLAG_DUPLICATE_IMAGE;
                break;
            }
        }

        // 4. EXIF GPS Mismatch (< 500 meters from site)
        if ($siteCoords) {
            foreach ($exifCoords as $coord) {
                if (!empty($coord['lat']) && !empty($coord['lng'])) {
                    $exifDist = $this->boundaryValidator->haversineDistanceMeters(
                        (float) $coord['lat'],
                        (float) $coord['lng'],
                        $siteCoords['lat'],
                        $siteCoords['lng']
                    );
                    if ($exifDist > 500.0) {
                        $flags[] = self::FLAG_EXIF_MISMATCH;
                        break;
                    }
                }
            }
        }

        return array_unique($flags);
    }
}

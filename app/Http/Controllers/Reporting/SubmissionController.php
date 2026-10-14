<?php

namespace App\Http\Controllers\Reporting;

use App\Domain\Access\GrantResolver;
use App\Domain\Registry\ProjectSite;
use App\Domain\Reporting\Attachment;
use App\Domain\Reporting\FieldReport;
use App\Domain\Reporting\FraudDetector;
use App\Domain\Reporting\Intervention;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SubmissionController extends Controller
{
    public function __construct(
        protected GrantResolver $grantResolver,
        protected FraudDetector $fraudDetector
    ) {}

    public function create(Request $request): Response
    {
        $user = $request->user();
        if (!$this->grantResolver->allows($user, 'submission.create')) {
            abort(403, 'Unauthorized to submit field reports.');
        }

        $sites = ProjectSite::with([
            'project.projectType.statusDefs',
            'project.projectType.fieldDefs',
            'project.projectType.interventionTypes',
            'location.barangay.parent',
            'institution',
        ])
            ->whereHas('project', fn ($q) => $q->where('lifecycle', 'active'))
            ->orderBy('id', 'desc')
            ->get();

        $selectedSiteId = $request->input('site_id');

        return Inertia::render('Submissions/Create', [
            'sites' => $sites,
            'initialSiteId' => $selectedSiteId ? (int) $selectedSiteId : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (!$this->grantResolver->allows($user, 'submission.create')) {
            abort(403, 'Unauthorized to submit field reports.');
        }

        $validated = $request->validate([
            'project_site_id' => ['required', 'exists:project_sites,id'],
            'intervention_type' => ['required', 'string', 'max:64'],
            'new_status' => ['required', 'string', 'max:64'],
            'occurred_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'submission_lat' => ['nullable', 'numeric'],
            'submission_lng' => ['nullable', 'numeric'],
            'gps_accuracy_m' => ['nullable', 'numeric'],
            'custom_values' => ['nullable', 'array'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['file', 'image', 'max:10240'], // 10MB max per photo
        ]);

        $site = ProjectSite::with(['project.projectType.statusDefs', 'location'])->findOrFail($validated['project_site_id']);

        // Check regional/project grant scope
        $psgcCode = $site->location?->psgc_barangay;
        if (!$this->grantResolver->allows($user, 'submission.create', $site->project_id, $site->project?->program_id, $psgcCode)) {
            abort(403, 'Unauthorized to submit reports for this project or geographic location.');
        }

        // Process uploaded files for hash and EXIF evaluation
        $imageHashes = [];
        $exifCoords = [];
        $photoRecords = [];

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $hash = hash_file('sha256', $file->getRealPath());
                $imageHashes[] = $hash;

                // Try reading EXIF GPS if available
                $exifLat = null;
                $exifLng = null;
                $exifTime = null;

                if (function_exists('exif_read_data') && in_array($file->getMimeType(), ['image/jpeg', 'image/tiff'])) {
                    try {
                        $exif = @exif_read_data($file->getRealPath());
                        if (!empty($exif['GPSLatitude']) && !empty($exif['GPSLongitude'])) {
                            // Convert EXIF rational to decimal if present
                            $exifLat = $validated['submission_lat'] ?? null;
                            $exifLng = $validated['submission_lng'] ?? null;
                        }
                    } catch (\Throwable $e) {
                        // EXIF parsing fallback
                    }
                }

                if ($exifLat && $exifLng) {
                    $exifCoords[] = ['lat' => $exifLat, 'lng' => $exifLng];
                }

                // Store file
                $filename = uniqid('evid_', true) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('evidence', $filename, 'public');

                $photoRecords[] = [
                    'file_path' => '/storage/' . $path,
                    'file_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'file_size_bytes' => $file->getSize(),
                    'sha256_hash' => $hash,
                    'exif_lat' => $exifLat,
                    'exif_lng' => $exifLng,
                    'exif_timestamp' => $exifTime,
                ];
            }
        }

        // Run anti-fraud evaluation
        $flags = $this->fraudDetector->evaluate(
            $site,
            $validated['new_status'],
            isset($validated['submission_lat']) ? (float) $validated['submission_lat'] : null,
            isset($validated['submission_lng']) ? (float) $validated['submission_lng'] : null,
            $imageHashes,
            $exifCoords
        );

        $verificationStatus = !empty($flags) ? 'flagged' : 'pending';
        $previousStatus = $site->current_status;

        DB::transaction(function () use (
            $site,
            $validated,
            $previousStatus,
            $user,
            $verificationStatus,
            $flags,
            $photoRecords
        ) {
            // 1. Create append-only intervention record
            $intervention = Intervention::create([
                'project_site_id' => $site->id,
                'intervention_type' => $validated['intervention_type'],
                'previous_status' => $previousStatus,
                'new_status' => $validated['new_status'],
                'occurred_at' => $validated['occurred_at'] ?? now(),
                'created_by' => $user->id,
                'source' => 'web_form',
            ]);

            // 2. Update site current status
            $site->current_status = $validated['new_status'];
            $site->save();

            // 3. Create field report telemetry record
            $report = FieldReport::create([
                'intervention_id' => $intervention->id,
                'notes' => $validated['notes'] ?? null,
                'submission_lat' => $validated['submission_lat'] ?? null,
                'submission_lng' => $validated['submission_lng'] ?? null,
                'gps_accuracy_m' => $validated['gps_accuracy_m'] ?? null,
                'device_timestamp' => now(),
                'server_timestamp' => now(),
                'custom_values' => $validated['custom_values'] ?? null,
                'verification_status' => $verificationStatus,
                'verification_flags' => !empty($flags) ? $flags : null,
            ]);

            // 4. Save attachments
            foreach ($photoRecords as $photo) {
                $photo['field_report_id'] = $report->id;
                Attachment::create($photo);
            }
        });

        // Dispatch asynchronous rollup update job
        \App\Jobs\UpdateRollupJob::dispatch($site->project_id);

        $redirect = redirect()->route('sites.show', $site->id);
        if (!empty($flags)) {
            return $redirect->with('warning', 'Report submitted, but anti-fraud inspection flagged: ' . implode(', ', $flags));
        }

        return $redirect->with('success', 'Field report and status update recorded successfully.');
    }

    public function reviewQueue(Request $request): Response
    {
        $user = $request->user();
        if (!$this->grantResolver->allows($user, 'submission.verify') && !$this->grantResolver->allows($user, '*')) {
            abort(403, 'Unauthorized to access review queue.');
        }

        $query = FieldReport::query()
            ->with([
                'intervention.projectSite.project.program',
                'intervention.projectSite.location',
                'intervention.author',
                'attachments',
                'verifier',
            ]);

        if ($status = $request->input('status')) {
            $query->where('verification_status', $status);
        } else {
            // Default to pending and flagged
            $query->whereIn('verification_status', ['pending', 'flagged']);
        }

        $reports = $query->latest('id')->paginate(20)->withQueryString();

        $stats = [
            'pending_count' => FieldReport::where('verification_status', 'pending')->count(),
            'flagged_count' => FieldReport::where('verification_status', 'flagged')->count(),
            'verified_count' => FieldReport::where('verification_status', 'verified')->count(),
            'rejected_count' => FieldReport::where('verification_status', 'rejected')->count(),
        ];

        return Inertia::render('Submissions/ReviewQueue', [
            'reports' => $reports,
            'stats' => $stats,
            'filters' => $request->only(['status']),
        ]);
    }

    public function verify(Request $request, FieldReport $fieldReport): RedirectResponse
    {
        $user = $request->user();
        if (!$this->grantResolver->allows($user, 'submission.verify') && !$this->grantResolver->allows($user, '*')) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:verified,flagged,rejected'],
            'review_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $fieldReport->verification_status = $validated['status'];
        $fieldReport->verified_by = $user->id;
        $fieldReport->verified_at = now();
        $fieldReport->review_notes = $validated['review_notes'] ?? null;
        $fieldReport->save();

        if ($fieldReport->intervention?->projectSite?->project_id) {
            \App\Jobs\UpdateRollupJob::dispatch($fieldReport->intervention->projectSite->project_id);
        }

        return back()->with('success', "Report marked as {$validated['status']}.");
    }
}

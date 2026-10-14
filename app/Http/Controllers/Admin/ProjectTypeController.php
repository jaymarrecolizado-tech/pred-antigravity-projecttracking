<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Access\GrantResolver;
use App\Domain\Portfolio\FieldDef;
use App\Domain\Portfolio\ProjectType;
use App\Domain\Portfolio\StatusDef;
use App\Domain\Portfolio\StatusWorkflow;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectTypeController extends Controller
{
    public function __construct(
        protected GrantResolver $grantResolver,
        protected StatusWorkflow $workflow
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        if (!$this->grantResolver->allows($user, '*')) {
            abort(403, 'Unauthorized. Super Admin access required.');
        }

        $projectTypes = ProjectType::query()
            ->withCount(['projects', 'statusDefs', 'fieldDefs', 'interventionTypes'])
            ->orderBy('id')
            ->get();

        return Inertia::render('Admin/ProjectTypes/Index', [
            'projectTypes' => $projectTypes,
        ]);
    }

    public function edit(Request $request, ProjectType $projectType): Response
    {
        $user = $request->user();
        if (!$this->grantResolver->allows($user, '*')) {
            abort(403, 'Unauthorized. Super Admin access required.');
        }

        $projectType->load([
            'statusDefs' => fn ($q) => $q->orderBy('sort_order'),
            'fieldDefs' => fn ($q) => $q->orderBy('sort_order'),
            'interventionTypes' => fn ($q) => $q->orderBy('id'),
        ]);

        $workflowErrors = $this->workflow->validateWorkflow($projectType);

        return Inertia::render('Admin/ProjectTypes/Edit', [
            'projectType' => $projectType,
            'workflowErrors' => $workflowErrors,
        ]);
    }

    public function update(Request $request, ProjectType $projectType): RedirectResponse
    {
        $user = $request->user();
        if (!$this->grantResolver->allows($user, '*')) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'marker_shape' => ['required', 'string', 'in:circle,square,diamond,triangle,pin'],
            'color_code' => ['required', 'string', 'max:9'],
            'description' => ['nullable', 'string'],
        ]);

        $projectType->update($validated);

        return back()->with('success', 'Project type updated successfully.');
    }

    public function storeStatus(Request $request, ProjectType $projectType): RedirectResponse
    {
        $user = $request->user();
        if (!$this->grantResolver->allows($user, '*')) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'id' => ['nullable', 'exists:status_defs,id'],
            'key' => ['required', 'string', 'max:64'],
            'label' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_completed' => ['boolean'],
            'is_terminal' => ['boolean'],
            'next_keys' => ['nullable', 'array'],
        ]);

        StatusDef::updateOrCreate(
            [
                'project_type_id' => $projectType->id,
                'key' => $validated['key'],
            ],
            [
                'label' => $validated['label'],
                'sort_order' => $validated['sort_order'],
                'is_completed' => $validated['is_completed'] ?? false,
                'is_terminal' => $validated['is_terminal'] ?? false,
                'next_keys' => !empty($validated['next_keys']) ? $validated['next_keys'] : null,
            ]
        );

        return back()->with('success', "Status '{$validated['label']}' saved successfully.");
    }

    public function deleteStatus(Request $request, ProjectType $projectType, StatusDef $statusDef): RedirectResponse
    {
        $user = $request->user();
        if (!$this->grantResolver->allows($user, '*')) {
            abort(403, 'Unauthorized.');
        }

        if ($statusDef->project_type_id !== $projectType->id) {
            abort(404);
        }

        $statusDef->delete();

        return back()->with('success', "Status '{$statusDef->label}' removed.");
    }

    public function storeField(Request $request, ProjectType $projectType): RedirectResponse
    {
        $user = $request->user();
        if (!$this->grantResolver->allows($user, '*')) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'id' => ['nullable', 'exists:field_defs,id'],
            'key' => ['required', 'string', 'max:64'],
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:text,number,integer,select,multiselect,date,boolean,sex_disaggregated'],
            'options' => ['nullable', 'array'],
            'required' => ['boolean'],
            'applies_to' => ['required', 'in:site,report,activity'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        FieldDef::updateOrCreate(
            [
                'project_type_id' => $projectType->id,
                'key' => $validated['key'],
            ],
            [
                'label' => $validated['label'],
                'type' => $validated['type'],
                'options' => !empty($validated['options']) ? $validated['options'] : null,
                'required' => $validated['required'] ?? false,
                'applies_to' => $validated['applies_to'],
                'sort_order' => $validated['sort_order'],
            ]
        );

        return back()->with('success', "Custom field '{$validated['label']}' saved successfully.");
    }

    public function deleteField(Request $request, ProjectType $projectType, FieldDef $fieldDef): RedirectResponse
    {
        $user = $request->user();
        if (!$this->grantResolver->allows($user, '*')) {
            abort(403, 'Unauthorized.');
        }

        if ($fieldDef->project_type_id !== $projectType->id) {
            abort(404);
        }

        $fieldDef->delete();

        return back()->with('success', "Field '{$fieldDef->label}' removed.");
    }
}

<?php

namespace Tests\Unit;

use App\Domain\Portfolio\ProjectType;
use App\Domain\Portfolio\StatusDef;
use App\Domain\Portfolio\StatusWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatusWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected StatusWorkflow $workflow;
    protected ProjectType $projectType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->workflow = new StatusWorkflow();

        $this->projectType = ProjectType::create([
            'name' => 'Test Flow',
            'kind' => 'site',
            'marker_shape' => 'circle',
            'color_code' => '#2563eb',
        ]);
    }

    public function test_initial_status_returns_first_by_sort_order(): void
    {
        StatusDef::create([
            'project_type_id' => $this->projectType->id,
            'key' => 'step_two',
            'label' => 'Step Two',
            'sort_order' => 20,
        ]);
        StatusDef::create([
            'project_type_id' => $this->projectType->id,
            'key' => 'step_one',
            'label' => 'Step One',
            'sort_order' => 10,
        ]);

        $this->projectType->load('statusDefs');

        $allowed = $this->workflow->allowedNext($this->projectType, null);
        $this->assertEquals(['step_one'], $allowed);
    }

    public function test_explicit_next_keys_takes_precedence_over_sequence(): void
    {
        StatusDef::create([
            'project_type_id' => $this->projectType->id,
            'key' => 'draft',
            'label' => 'Draft',
            'sort_order' => 1,
            'next_keys' => ['submitted', 'cancelled'],
        ]);
        StatusDef::create([
            'project_type_id' => $this->projectType->id,
            'key' => 'submitted',
            'label' => 'Submitted',
            'sort_order' => 2,
            'next_keys' => ['approved'],
        ]);
        StatusDef::create([
            'project_type_id' => $this->projectType->id,
            'key' => 'cancelled',
            'label' => 'Cancelled',
            'sort_order' => 3,
            'is_terminal' => true,
        ]);
        StatusDef::create([
            'project_type_id' => $this->projectType->id,
            'key' => 'approved',
            'label' => 'Approved',
            'sort_order' => 4,
            'is_completed' => true,
            'is_terminal' => true,
        ]);

        $this->projectType->load('statusDefs');

        // From 'draft', can go to submitted or cancelled
        $this->assertTrue($this->workflow->canTransition($this->projectType, 'draft', 'submitted'));
        $this->assertTrue($this->workflow->canTransition($this->projectType, 'draft', 'cancelled'));
        $this->assertFalse($this->workflow->canTransition($this->projectType, 'draft', 'approved'));

        // From terminal 'cancelled', no next transitions
        $this->assertEmpty($this->workflow->allowedNext($this->projectType, 'cancelled'));
        $this->assertFalse($this->workflow->canTransition($this->projectType, 'cancelled', 'draft'));
    }

    public function test_workflow_validation_detects_unknown_next_keys_and_missing_completed_state(): void
    {
        // Setup workflow with broken link and no is_completed
        StatusDef::create([
            'project_type_id' => $this->projectType->id,
            'key' => 'step_a',
            'label' => 'Step A',
            'sort_order' => 1,
            'is_completed' => false,
            'next_keys' => ['non_existent_key'],
        ]);

        $this->projectType->load('statusDefs');

        $errors = $this->workflow->validateWorkflow($this->projectType);

        $this->assertCount(2, $errors);
        $this->assertStringContainsString('references unknown next status', $errors[0]);
        $this->assertStringContainsString('no status marked as \'is_completed\'', $errors[1]);
    }

    public function test_calculate_progress_percentage(): void
    {
        StatusDef::create([
            'project_type_id' => $this->projectType->id,
            'key' => 'step_1',
            'label' => 'Step 1',
            'sort_order' => 1,
            'is_completed' => false,
        ]);
        StatusDef::create([
            'project_type_id' => $this->projectType->id,
            'key' => 'step_2',
            'label' => 'Step 2',
            'sort_order' => 2,
            'is_completed' => false,
        ]);
        StatusDef::create([
            'project_type_id' => $this->projectType->id,
            'key' => 'step_3',
            'label' => 'Step 3',
            'sort_order' => 3,
            'is_completed' => true,
        ]);

        $this->projectType->load('statusDefs');

        $this->assertEquals(33.3, $this->workflow->calculateProgress($this->projectType, 'step_1'));
        $this->assertEquals(66.7, $this->workflow->calculateProgress($this->projectType, 'step_2'));
        $this->assertEquals(100.0, $this->workflow->calculateProgress($this->projectType, 'step_3'));
    }
}

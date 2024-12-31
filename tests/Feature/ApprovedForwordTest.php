<?php
namespace Tests\Feature;

use App\Models\ApprovalChain;
use App\Models\ApprovalChainStep;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ApprovedForwordTest extends TestCase
{

    /**
     *
     * @return void
     */
    public function test_approve_and_forward_step_successfully()
    {
        $user = User::factory()->create();

        $project = Project::factory()->create();
        $approvalChain = ApprovalChain::factory()->create(['project_id' => $project->id]);

        $step = ApprovalChainStep::factory()->create([
            'approval_chain_id' => $approvalChain->id,
            'user_id' => $user->id,
            'step_order' => 1,
        ]);

        $this->actingAs($user);

        $response = $this->postJson("/approval-chains/approve-and-forward/{$step->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Step approved successfully and forwarded.',
        ]);

        $step->refresh();
        $this->assertTrue($step->approved);
    }

    /**
     *
     * @return void
     */
    public function test_approve_step_not_allowed()
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();

        $project = Project::factory()->create();
        $approvalChain = ApprovalChain::factory()->create(['project_id' => $project->id]);

        $step = ApprovalChainStep::factory()->create([
            'approval_chain_id' => $approvalChain->id,
            'user_id' => $anotherUser->id,
            'step_order' => 1,
        ]);

        $this->actingAs($user);

        $response = $this->postJson("/approval-chains/approve-and-forward/{$step->id}");

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Not allowed to approve this step.',
        ]);
    }

    /**
     *
     * @return void
     */
    public function test_project_status_updated_when_all_steps_approved()
    {
        $user = User::factory()->create();

        $project = Project::factory()->create();
        $approvalChain = ApprovalChain::factory()->create(['project_id' => $project->id]);

        $step1 = ApprovalChainStep::factory()->create([
            'approval_chain_id' => $approvalChain->id,
            'user_id' => $user->id,
            'step_order' => 1,
        ]);
        $step2 = ApprovalChainStep::factory()->create([
            'approval_chain_id' => $approvalChain->id,
            'user_id' => $user->id,
            'step_order' => 2,
        ]);

        $this->actingAs($user);

        $this->postJson("/approval-chains/approve-and-forward/{$step1->id}");

        $this->postJson("/approval-chains/approve-and-forward/{$step2->id}");

        $project->refresh();
        $this->assertEquals(2, $project->status_id);
    }
}

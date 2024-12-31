<?php


namespace Tests\Feature;

use App\Models\User;
use App\Models\ApprovalChain;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CreateApprovalChainTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate');
        $this->artisan('db:seed');

        $role = Role::firstOrCreate(['name' => 'Default role']);

        $project = Project::factory()->create();

        $user = User::factory()->create();
        $user->assignRole($role);

        $approvalChain = ApprovalChain::create([
            'project_id' => $project->id
        ]);

        $orderedUsers = $project->users()->pluck('id')->unique();
        $stepOrder = 1;
        foreach ($orderedUsers as $userId) {
            $approvalChain->steps()->create([
                'user_id' => $userId,
                'step_order' => $stepOrder++,
            ]);
        }
    }

    public function test_it_creates_approval_chain_for_authorized_user()
    {
        $user = User::first();

        $response = $this->actingAs($user)->postJson('/approval-chains/create', [
            'project_id' => 1,
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
    }

    public function test_it_returns_403_for_users_with_invalid_role()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/approval-chains/create', [
            'project_id' => 1,
        ]);

        $response->assertStatus(403);
        $response->assertJson(['error' => 'Not authorized to create approval chains']);
    }

    public function test_it_handles_errors_during_approval_chain_creation()
    {
        $user = User::first();

        $this->mock(\App\Services\ApprovalChainService::class, function ($mock) {
            $mock->shouldReceive('createApprovalChain')->andThrow(new \Exception('Some error occurred'));
        });

        $response = $this->actingAs($user)->postJson('/approval-chains/create', [
            'project_id' => 1,
        ]);

        $response->assertStatus(500);
        $response->assertJson(['success' => false, 'message' => 'Some error occurred']);
    }
}

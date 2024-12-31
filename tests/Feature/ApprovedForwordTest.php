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
     * اختبار الموافقة على الخطوة وإحالتها بنجاح.
     *
     * @return void
     */
    public function test_approve_and_forward_step_successfully()
    {
        // إنشاء مستخدم
        $user = User::factory()->create();

        // إنشاء مشروع وسلسلة موافقة
        $project = Project::factory()->create();
        $approvalChain = ApprovalChain::factory()->create(['project_id' => $project->id]);

        // إنشاء خطوة في سلسلة الموافقة
        $step = ApprovalChainStep::factory()->create([
            'approval_chain_id' => $approvalChain->id,
            'user_id' => $user->id,
            'step_order' => 1,
        ]);

        // تسجيل دخول المستخدم
        $this->actingAs($user);

        // إرسال طلب للموافقة والإحالة على الخطوة
        $response = $this->postJson("/approval-chains/approve-and-forward/{$step->id}");

        // التحقق من أن الاستجابة كانت ناجحة
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Step approved successfully and forwarded.',
        ]);

        // التحقق من أن الخطوة تم الموافقة عليها
        $step->refresh();
        $this->assertTrue($step->approved);
    }

    /**
     * اختبار محاولة الموافقة على خطوة غير مسموح بها.
     *
     * @return void
     */
    public function test_approve_step_not_allowed()
    {
        // إنشاء مستخدمين
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();

        // إنشاء مشروع وسلسلة موافقة
        $project = Project::factory()->create();
        $approvalChain = ApprovalChain::factory()->create(['project_id' => $project->id]);

        // إنشاء خطوة في سلسلة الموافقة
        $step = ApprovalChainStep::factory()->create([
            'approval_chain_id' => $approvalChain->id,
            'user_id' => $anotherUser->id,
            'step_order' => 1,
        ]);

        // تسجيل دخول المستخدم الأول
        $this->actingAs($user);

        // محاولة الموافقة على خطوة لا يملك هذا المستخدم حق الموافقة عليها
        $response = $this->postJson("/approval-chains/approve-and-forward/{$step->id}");

        // التحقق من أن الاستجابة كانت خطأ 403
        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Not allowed to approve this step.',
        ]);
    }

    /**
     * اختبار حالة المشروع بعد الانتهاء من جميع الخطوات.
     *
     * @return void
     */
    public function test_project_status_updated_when_all_steps_approved()
    {
        // إنشاء مستخدم
        $user = User::factory()->create();

        // إنشاء مشروع وسلسلة موافقة
        $project = Project::factory()->create();
        $approvalChain = ApprovalChain::factory()->create(['project_id' => $project->id]);

        // إنشاء خطوات في سلسلة الموافقة
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

        // تسجيل دخول المستخدم
        $this->actingAs($user);

        // الموافقة على الخطوة الأولى
        $this->postJson("/approval-chains/approve-and-forward/{$step1->id}");

        // الموافقة على الخطوة الثانية
        $this->postJson("/approval-chains/approve-and-forward/{$step2->id}");

        // التحقق من أن حالة المشروع تم تحديثها إلى "موافق عليه"
        $project->refresh();
        $this->assertEquals(2, $project->status_id);
    }
}

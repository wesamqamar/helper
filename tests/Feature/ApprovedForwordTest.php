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
<<<<<<< HEAD
=======
     * اختبار الموافقة على الخطوة وإحالتها بنجاح.
>>>>>>> ddfc25b69e4174cef14b0d98d5d1419bcb034011
     *
     * @return void
     */
    public function test_approve_and_forward_step_successfully()
    {
<<<<<<< HEAD
        $user = User::factory()->create();

        $project = Project::factory()->create();
        $approvalChain = ApprovalChain::factory()->create(['project_id' => $project->id]);

=======
        // إنشاء مستخدم
        $user = User::factory()->create();

        // إنشاء مشروع وسلسلة موافقة
        $project = Project::factory()->create();
        $approvalChain = ApprovalChain::factory()->create(['project_id' => $project->id]);

        // إنشاء خطوة في سلسلة الموافقة
>>>>>>> ddfc25b69e4174cef14b0d98d5d1419bcb034011
        $step = ApprovalChainStep::factory()->create([
            'approval_chain_id' => $approvalChain->id,
            'user_id' => $user->id,
            'step_order' => 1,
        ]);

<<<<<<< HEAD
        $this->actingAs($user);

        $response = $this->postJson("/approval-chains/approve-and-forward/{$step->id}");

=======
        // تسجيل دخول المستخدم
        $this->actingAs($user);

        // إرسال طلب للموافقة والإحالة على الخطوة
        $response = $this->postJson("/approval-chains/approve-and-forward/{$step->id}");

        // التحقق من أن الاستجابة كانت ناجحة
>>>>>>> ddfc25b69e4174cef14b0d98d5d1419bcb034011
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Step approved successfully and forwarded.',
        ]);

<<<<<<< HEAD
=======
        // التحقق من أن الخطوة تم الموافقة عليها
>>>>>>> ddfc25b69e4174cef14b0d98d5d1419bcb034011
        $step->refresh();
        $this->assertTrue($step->approved);
    }

    /**
<<<<<<< HEAD
=======
     * اختبار محاولة الموافقة على خطوة غير مسموح بها.
>>>>>>> ddfc25b69e4174cef14b0d98d5d1419bcb034011
     *
     * @return void
     */
    public function test_approve_step_not_allowed()
    {
<<<<<<< HEAD
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();

        $project = Project::factory()->create();
        $approvalChain = ApprovalChain::factory()->create(['project_id' => $project->id]);

=======
        // إنشاء مستخدمين
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();

        // إنشاء مشروع وسلسلة موافقة
        $project = Project::factory()->create();
        $approvalChain = ApprovalChain::factory()->create(['project_id' => $project->id]);

        // إنشاء خطوة في سلسلة الموافقة
>>>>>>> ddfc25b69e4174cef14b0d98d5d1419bcb034011
        $step = ApprovalChainStep::factory()->create([
            'approval_chain_id' => $approvalChain->id,
            'user_id' => $anotherUser->id,
            'step_order' => 1,
        ]);

<<<<<<< HEAD
        $this->actingAs($user);

        $response = $this->postJson("/approval-chains/approve-and-forward/{$step->id}");

=======
        // تسجيل دخول المستخدم الأول
        $this->actingAs($user);

        // محاولة الموافقة على خطوة لا يملك هذا المستخدم حق الموافقة عليها
        $response = $this->postJson("/approval-chains/approve-and-forward/{$step->id}");

        // التحقق من أن الاستجابة كانت خطأ 403
>>>>>>> ddfc25b69e4174cef14b0d98d5d1419bcb034011
        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Not allowed to approve this step.',
        ]);
    }

    /**
<<<<<<< HEAD
=======
     * اختبار حالة المشروع بعد الانتهاء من جميع الخطوات.
>>>>>>> ddfc25b69e4174cef14b0d98d5d1419bcb034011
     *
     * @return void
     */
    public function test_project_status_updated_when_all_steps_approved()
    {
<<<<<<< HEAD
        $user = User::factory()->create();

        $project = Project::factory()->create();
        $approvalChain = ApprovalChain::factory()->create(['project_id' => $project->id]);

=======
        // إنشاء مستخدم
        $user = User::factory()->create();

        // إنشاء مشروع وسلسلة موافقة
        $project = Project::factory()->create();
        $approvalChain = ApprovalChain::factory()->create(['project_id' => $project->id]);

        // إنشاء خطوات في سلسلة الموافقة
>>>>>>> ddfc25b69e4174cef14b0d98d5d1419bcb034011
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

<<<<<<< HEAD
        $this->actingAs($user);

        $this->postJson("/approval-chains/approve-and-forward/{$step1->id}");

        $this->postJson("/approval-chains/approve-and-forward/{$step2->id}");

=======
        // تسجيل دخول المستخدم
        $this->actingAs($user);

        // الموافقة على الخطوة الأولى
        $this->postJson("/approval-chains/approve-and-forward/{$step1->id}");

        // الموافقة على الخطوة الثانية
        $this->postJson("/approval-chains/approve-and-forward/{$step2->id}");

        // التحقق من أن حالة المشروع تم تحديثها إلى "موافق عليه"
>>>>>>> ddfc25b69e4174cef14b0d98d5d1419bcb034011
        $project->refresh();
        $this->assertEquals(2, $project->status_id);
    }
}

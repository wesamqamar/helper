<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateApprovalChainRequest;
use App\Models\ApprovalChainStep;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ApprovalChain;
use App\Models\Project;
use App\Models\ProjectUser;
use Illuminate\Support\Facades\DB;
use Exception;


class ApprovalChainController extends Controller
{
    public function listApprovalChains(Request $request)
    {
        $userId = Auth::id();

        $user = User::with('roles')->find($userId);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $userRole = $user->roles->first()->name ?? 'Default role';

        $query = ApprovalChainStep::query();

        if ($userRole === 'Default role') {
            $approvalChains = $query
                ->with(['approvalChain', 'user'])
                ->whereHas('user', function ($query) {
                })
                ->orderBy('approval_chain_id')
                ->orderBy('step_order')
                ->get()
                ->map(function ($approvalChainStep) {
                    return [
                        'approved' => $approvalChainStep->approved,
                        'approved_at' => $approvalChainStep->approved_at,
                        'user_name' => $approvalChainStep->user->name,
                        'project_name' => $approvalChainStep->approvalChain->project->name,
                    ];
                });

        } else {
            $approvalChains = $query
                ->whereHas('user', function ($query) use ($userId) {
                    $query->where('id', $userId);
                })
                ->orderBy('approval_chain_id')
                ->orderBy('step_order')
                ->get()
                ->map(function ($approvalChainStep) {
                    return [
                        'approved' => $approvalChainStep->approved,
                        'approved_at' => $approvalChainStep->approved_at,
                        'project_name' => $approvalChainStep->approvalChain->project->name,
                    ];
                });
        }

        return response()->json(['data' => $approvalChains], 200);
    }


    public function createApprovalChain(CreateApprovalChainRequest $request)
    {
        $userId = Auth::id();
        $user = User::with('roles')->find($userId);
        $userRole = $user->roles->first()->name ?? 'Default role';

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        if ($userRole !== 'Default role') {
            return response()->json(['error' => 'You are not allowed to create approval chains'], 403);
        }

        $validatedData = $request->validated();

        return DB::transaction(function () use ($validatedData) {
            try {
                $approvalChain = ApprovalChain::create([
                    'project_id' => $validatedData['project_id'],
                ]);
                       // ربط الخطوات
                       $orderedUsers = $approvalChain->project->users()->pluck('user_id')->unique();

                       $stepOrder = 1;
                       foreach ($orderedUsers as $userId) {
                           $approvalChain->steps()->create([
                               'user_id' => $userId,
                               'step_order' => $stepOrder,
                           ]);
                           $stepOrder++;
                       }

                       // جلب البيانات مع العلاقات
                       $approvalChain->load('project', 'steps.user');


                return response()->json([
                    'success' => true,
                    'message' => 'Approval chain created successfully.',
                    'data' => $approvalChain,
                ], 201);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }
        });
    }

    public function approveAndForwardStep(Request $request, $stepId)
    {
        try {
            $step = ApprovalChainStep::findOrFail($stepId);

            $currentStep = ApprovalChainStep::where('approval_chain_id', $step->approval_chain_id)
                ->where('approved', false)
                ->orderBy('step_order', 'asc')
                ->first();

            if (!$currentStep || $step->id !== $currentStep->id || $step->user_id !== auth()->id()) {
                return response()->json(['error' => 'Not allow to approve this step'], 403);
            }

            $step->update([
                'approved' => true,
                'approved_at' => now(),
            ]);

            $nextStep = ApprovalChainStep::where('approval_chain_id', $step->approval_chain_id)
                ->where('step_order', '>', $step->step_order)
                ->orderBy('step_order', 'asc')
                ->first();

            if (!$nextStep) {
                $allStepsApproved = ApprovalChainStep::where('approval_chain_id', $step->approval_chain_id)
                    ->where('approved', false)
                    ->doesntExist();

                if ($allStepsApproved) {
                    $project = Project::find($step->approvalChain->project_id);
                    $project->update(['status_id' => 2]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Step approved successfully and forwarded.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


}

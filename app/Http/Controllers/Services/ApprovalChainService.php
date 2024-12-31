<?php

namespace App\Services;

use App\Models\ApprovalChain;
use App\Models\ApprovalChainStep;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApprovalChainService
{
    public function listApprovalChains($user)
    {
        $query = ApprovalChainStep::query();

        if ($user->roles->first()->name === 'Default role') {
            return $query->with(['approvalChain', 'user'])
                ->orderBy('approval_chain_id')
                ->orderBy('step_order')
                ->get()
                ->map(function ($step) {
                    return [
                        'approved' => $step->approved,
                        'approved_at' => $step->approved_at,
                        'user_name' => $step->user->name,
                        'project_name' => $step->approvalChain->project->name,
                    ];
                });
        } else {
            return $query->whereHas('user', fn($q) => $q->where('id', Auth::id()))
                ->orderBy('approval_chain_id')
                ->orderBy('step_order')
                ->get()
                ->map(function ($step) {
                    return [
                        'approved' => $step->approved,
                        'approved_at' => $step->approved_at,
                        'project_name' => $step->approvalChain->project->name,
                    ];
                });
        }
    }

    public function createApprovalChain($validatedData)
    {
        return DB::transaction(function () use ($validatedData) {
            $approvalChain = ApprovalChain::create(['project_id' => $validatedData['project_id']]);
            $orderedUsers = $approvalChain->project->users()->pluck('user_id')->unique();

            $stepOrder = 1;
            foreach ($orderedUsers as $userId) {
                $approvalChain->steps()->create([
                    'user_id' => $userId,
                    'step_order' => $stepOrder++,
                ]);
            }

            return $approvalChain->load('project', 'steps.user');
        });
    }

    public function approveAndForwardStep($stepId)
    {
        $step = ApprovalChainStep::findOrFail($stepId);

        if ($step->user_id !== Auth::id() || $step->approved) {
            throw new \Exception('Not allowed to approve this step.');
        }

        $step->update(['approved' => true, 'approved_at' => now()]);

        $nextStep = ApprovalChainStep::where('approval_chain_id', $step->approval_chain_id)
            ->where('step_order', '>', $step->step_order)
            ->orderBy('step_order')
            ->first();

        if (!$nextStep) {
            $allStepsApproved = ApprovalChainStep::where('approval_chain_id', $step->approval_chain_id)
                ->where('approved', false)
                ->doesntExist();

            if ($allStepsApproved) {
                $step->approvalChain->project->update(['status_id' => 2]);
            }
        }

        return true;
    }
}

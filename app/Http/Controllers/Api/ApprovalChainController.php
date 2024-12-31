<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateApprovalChainRequest;
use App\Services\ApprovalChainService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalChainController extends Controller
{
    protected $approvalChainService;

    public function __construct(ApprovalChainService $approvalChainService)
    {
        $this->approvalChainService = $approvalChainService;
    }

    public function listApprovalChains(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $approvalChains = $this->approvalChainService->listApprovalChains($user);

        return response()->json(['data' => $approvalChains], 200);
    }

    public function createApprovalChain(CreateApprovalChainRequest $request)
    {
        $user = Auth::user();

        if (!$user || $user->roles->first()->name !== 'Default role') {
            return response()->json(['error' => 'Not authorized to create approval chains'], 403);
        }

        $validatedData = $request->validated();

        try {
            $approvalChain = $this->approvalChainService->createApprovalChain($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Approval chain created successfully.',
                'data' => $approvalChain,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function approveAndForwardStep(Request $request, $stepId)
    {
        try {
            $this->approvalChainService->approveAndForwardStep($stepId);

            return response()->json([
                'success' => true,
                'message' => 'Step approved successfully and forwarded.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}

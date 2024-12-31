<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApprovalChainController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (!Auth::attempt($credentials)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $user = Auth::user();

    $token = $user->createToken('API Token')->plainTextToken;

    return response()->json([
        'message' => 'Logged in successfully',
        'user' => $user,
        'token' => $token,
    ]);
});
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



Route::prefix('/approval-chains')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [ApprovalChainController::class, 'listApprovalChains']);
    Route::post('/create', [ApprovalChainController::class, 'createApprovalChain']);
    Route::post('/approve-and-forward/{stepId}', [ApprovalChainController::class, 'approveAndForwardStep']);

});

<?php

namespace App\Http\Controllers;

use App\Http\Requests\Reaction\StoreReactionRequest;
use App\Http\Resources\ReactionResource;
use App\Services\ReactionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class ReactionController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ReactionService $reactionService
    ) {
    }

    public function toggle(StoreReactionRequest $request): JsonResponse
    {
        $result = $this->reactionService->toggle(
            userId: $request->user()->id,
            reactableType: $request->string('reactable_type')->toString(),
            reactableId: $request->integer('reactable_id'),
            type: $request->integer('type')
        );

        if ($result['action'] === 'removed') {
            return $this->successResponse(
                message: 'Reaction removed successfully'
            );
        }

        return $this->successResponse(
            data: new ReactionResource($result['reaction']),
            message: 'Reaction created successfully',
            statusCode: 201
        );
    }
}

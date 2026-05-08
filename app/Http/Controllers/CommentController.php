<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Services\CommentService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected CommentService $commentService
    ) {
    }

    public function index(Request $request, int $post): JsonResponse
    {
        $comments = $this->commentService->getForPost($post, $request->user()->id);

        return $this->successResponse(
            data: CommentResource::collection($comments),
            message: 'Comments fetched successfully'
        );
    }

    public function store(StoreCommentRequest $request, int $post): JsonResponse
    {
        $comment = $this->commentService->create(
            postId: $post,
            userId: $request->user()->id,
            attributes: $request->validated()
        );

        return $this->successResponse(
            data: new CommentResource($comment),
            message: 'Comment created successfully',
            statusCode: 201
        );
    }
}

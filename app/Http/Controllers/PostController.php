<?php

namespace App\Http\Controllers;

use App\Http\Requests\Post\StorePostRequest;
use App\Http\Resources\PostResource;
use App\Services\PostService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class PostController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PostService $postService
    ) {
    }

    public function store(StorePostRequest $request): JsonResponse
    {
        $post = $this->postService->create(
            userId: $request->user()->id,
            attributes: $request->validated()
        );

        return $this->successResponse(
            data: new PostResource($post),
            message: 'Post created successfully',
            statusCode: 201
        );
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Services\FeedService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected FeedService $feedService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $posts = $this->feedService->getForUser($request->user()->id);

        return $this->successResponse(
            data: PostResource::collection($posts),
            message: 'Feed fetched successfully'
        );
    }
}

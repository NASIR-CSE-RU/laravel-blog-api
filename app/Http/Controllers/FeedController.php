<?php

namespace App\Http\Controllers;

use App\Http\Requests\Feed\PaginateFeedsRequest;
use App\Http\Resources\PostResource;
use App\Services\FeedService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class FeedController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected FeedService $feedService
    ) {
    }

    public function index(PaginateFeedsRequest $request): JsonResponse
    {
        $posts = $this->feedService->getForUser($request->user()->id, $request->perPage());

        return $this->successResponse(
            data: PostResource::collection($posts->getCollection()),
            message: 'Feed fetched successfully',
            meta: [
                'pagination' => [
                    'total' => $posts->total(),
                    'count' => $posts->count(),
                    'per_page' => $posts->perPage(),
                    'current_page' => $posts->currentPage(),
                    'total_pages' => $posts->lastPage(),
                    'has_more_pages' => $posts->hasMorePages(),
                ],
            ]
        );
    }
}

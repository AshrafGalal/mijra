<?php

namespace App\Http\Controllers\Api\Tenant\Conversation;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\Conversation\Story\ContactStoryResource;
use App\Services\Tenant\StoryService;
use Illuminate\Http\Request;

class StoryController extends Controller
{
    public function __construct(protected StoryService $storyService)
    {
    }

    public function index(Request $request)
    {
        $filters = array_filter($request->all(), fn ($value) => filled($value));
        return ApiResponse::success(data: ContactStoryResource::collection($this->storyService->list($filters)));
    }

}

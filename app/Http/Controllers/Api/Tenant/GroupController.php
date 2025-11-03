<?php

namespace App\Http\Controllers\Api\Tenant;

use App\DTOs\Tenant\GroupDTO;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\GroupRequest;
use App\Http\Resources\Tenant\GroupResource;
use App\Services\Tenant\GroupService;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function __construct(protected readonly GroupService $groupService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();
        $groups = $this->groupService->groups(filters: $filters);

        return ApiResponse::success(data: GroupResource::collection($groups));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @throws \Throwable
     */
    public function store(GroupRequest $request)
    {
        $groupDTO = GroupDTO::fromRequest($request);
        $this->groupService->create($groupDTO);

        return ApiResponse::success(message: __('app.group_created_successfully'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $group = $this->groupService->findById(id: $id);

        return ApiResponse::success(data: GroupResource::make($group));
    }

    /**
     * Update the specified resource in storage.
     *
     * @throws \Throwable
     */
    public function update(GroupRequest $request, int $group)
    {
        $groupDTO = GroupDTO::fromRequest($request);
        $this->groupService->update(group: $group, groupDTO: $groupDTO);

        return ApiResponse::success(message: __('app.group_updated_successfully ✅'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->groupService->delete($id);

        return ApiResponse::success(message: 'Group deleted successfully ✅');

    }
}

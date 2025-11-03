<?php

namespace App\Services\Tenant;

use App\DTOs\Tenant\StoryDTO;
use App\Models\Tenant\Story;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class StoryService extends BaseService
{
    protected function getFilterClass(): ?string
    {
        return null;
    }

    protected function baseQuery(): Builder
    {
        return Story::query();
    }

    /**
     * @throws \Throwable
     */
    public function create(StoryDTO $dto): Story
    {
        return DB::connection('tenant')
            ->transaction(function () use ($dto) {
                // first create story
                $story = $this->baseQuery()->create($dto->toArray());
                $this->handleMedia($story, $dto);
                return $story;
            });
    }


    private function handleMedia(Story $message, StoryDTO $storyDTO): void
    {
        // ✅ If media file exists, attach via Spatie Media Library
        if (empty($storyDTO->mediaData)) {
            return;
        }

        $path = storage_path(str_replace('storage/', '', $storyDTO->mediaData['local_path']));
        $message
            ->addMedia($path)
            ->toMediaCollection('whatsapp');
    }


    public function list($filters = [])
    {
        $stories = $this->getQuery($filters)->with('media')
            ->orderByDesc('created_at')
            ->get();

        return $stories->groupBy('contact_identifier_id')
            ->map(function ($stories) {
                $story = $stories->first();

                return [
                    'contact_identifier_id' => $story->contact_identifier_id,
                    'contact_name' => $story->contact_name,
                    'stories' => $stories,
                ];
            })->values();
    }

}

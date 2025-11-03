<?php

namespace App\Services\Tenant\Actions\Template;

use App\DTOs\Tenant\TemplateDTO;
use App\Models\Tenant\Template;
use App\Models\Tenant\TemplateButton;
use App\Models\Tenant\TemplateVariable;
use App\Services\UploadFileService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreateTemplateService
{
    public function __construct(private readonly UploadFileService $uploadFileService) {}

    private function getQuery(): Builder
    {
        return Template::query();
    }

    /**
     * @throws \Throwable
     */
    public function handle(TemplateDTO $templateDTO)
    {
        return DB::connection('tenant')->transaction(function () use ($templateDTO) {
            $template = $this->getQuery()->create($templateDTO->toArray());

            if (! empty($templateDTO->media_id)) {
                $this->uploadFileService->assignMediaToModel(model: $template, media_ids: [$templateDTO->media_id], collection_name: 'templates');
            }

            $this->createTemplateButtons($template->id, $templateDTO->template_buttons);
            $this->createTemplateParameters($template->id, $templateDTO->template_parms);

            return $template;
        });
    }

    private function createTemplateButtons(string $templateId, ?array $templateButtons): void
    {
        if (empty($templateButtons)) {
            return;
        }

        $templateButtonsData = [];
        foreach ($templateButtons as $button) {
            $templateButtonsData[] = [
                'template_id' => $templateId,
                'button_type' => Arr::get($button, 'button_type'),
                'button_text' => Arr::get($button, 'button_text'),
                'action_value' => Arr::get($button, 'action_value'),
                'background_color' => Arr::get($button, 'background_color'),
                'text_color' => Arr::get($button, 'text_color'),
                'sort_order' => Arr::get($button, 'sort_order', 1),
            ];
        }

        TemplateButton::query()->insert($templateButtonsData);
    }

    private function createTemplateParameters(string $templateId, ?array $templateParams): void
    {
        if (empty($templateParams)) {
            return;
        }

        $templateParamsData = [];
        foreach ($templateParams as $param) {
            $templateParamsData[] = [
                'template_id' => $templateId,
                'variable_name' => Arr::get($param, 'parm_name'),
                'is_required' => Arr::get($param, 'is_required', true),
                'source' => Arr::get($param, 'integration_source'),
            ];
        }

        TemplateVariable::query()->insert($templateParamsData);
    }
}

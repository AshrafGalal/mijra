<?php

namespace App\DTOs\Tenant;

use App\DTOs\Abstract\BaseDTO;
use App\Enum\ActivationStatusEnum;
use App\Enum\ButtonTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Nette\Schema\ValidationException;

class TemplateDTO extends BaseDTO
{
    public function __construct(
        public string $name,
        public string $category,
        public string $template_type,// whatsapp,template
        public string $body,
        public ?string $sender_number = null,
        public ?string $header_content = null,
        public ?string $footer_content = null,
        public ?string $description = null,
        public ?array $template_buttons = [],
        public ?array $template_parms = null,
        public ?string $media_id = null,
        public bool $is_active = ActivationStatusEnum::ACTIVE->value,
    ) {}

    public static function fromRequest(Request $request): static
    {
        return new self(
            name: $request->name,
            category: $request->category,
            template_type: $request->template_type,
            body: $request->body,
            sender_number: $request->sender_number,
            header_content: $request->header_content,
            footer_content: $request->footer_content,
            template_buttons: $request->template_buttons ?? [],
            template_parms: $request->template_parms,
            media_id: $request->media_id,
            is_active: $request->is_active ?? ActivationStatusEnum::ACTIVE->value,
        );
    }

    public static function fromArray(array $data): static
    {
        return new self(
            name: Arr::get($data, 'name'),
            category: Arr::get($data, 'category'),
            template_type: Arr::get($data, 'template_type'),
            body: Arr::get($data, 'body'),
            sender_number: Arr::get($data, 'sender_number'),
            header_content: Arr::get($data, 'header_content'),
            footer_content: Arr::get($data, 'footer_content'),
            template_buttons: Arr::get($data, 'template_buttons', []),
            template_parms: Arr::get($data, 'template_parms'),
            media_id: Arr::get($data, 'media_id'),
            is_active: Arr::get($data, 'is_active', ActivationStatusEnum::ACTIVE->value),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'category' => $this->category,
            'template_type' => $this->template_type,
            'content' => $this->body,
            'sender_number' => $this->sender_number,
            'header_content' => $this->header_content,
            'footer_content' => $this->footer_content,
            'is_active' => $this->is_active,
        ];
    }

    /**
     * Validate and prepare template buttons data
     *
     * @throws ValidationException
     */
    public function validateAndPrepareTemplateButtons(array $templateButtons): array
    {
        $validatedButtons = [];
        $errors = [];

        foreach ($templateButtons as $index => $button) {
            $buttonIndex = $index + 1;

            // Prepare button data with default values
            $buttonData = [
                'button_type' => Arr::get($button, 'button_type'),
                'button_text' => Arr::get($button, 'button_text'),
                'action_value' => Arr::get($button, 'action_value'),
                //                'background_color' => Arr::get($button, 'background_color'),
                //                'text_color' => Arr::get($button, 'text_color'),
                'sort_order' => $buttonIndex,
            ];

            // Validate individual button
            $buttonErrors = $this->validateSingleButton($buttonData, $buttonIndex);
            if (! empty($buttonErrors)) {
                foreach ($buttonErrors as $field => $message) {
                    $errors["template_buttons.{$index}.{$field}"] = $message;
                }
            }

            $validatedButtons[] = $buttonData;
        }

        // Throw validation exception if there are errors
        if (! empty($errors)) {
            $validator = Validator::make([], []);
            foreach ($errors as $field => $message) {
                $validator->errors()->add($field, $message);
            }
            throw new ValidationException($validator);
        }

        return $validatedButtons;
    }

    /**
     * Validate a single button data
     */
    private function validateSingleButton(array $buttonData, int $buttonIndex): array
    {
        $errors = [];

        // Validate required fields
        if (empty($buttonData['button_type'])) {
            $errors['button_type'] = __('template.validation.button_type_required', ['position' => $buttonIndex]);
        }

        if (empty($buttonData['button_text'])) {
            $errors['button_text'] = __('template.validation.button_text_required', ['position' => $buttonIndex]);
        }

        if (empty($buttonData['action_value'])) {
            $errors['action_value'] = __('template.validation.action_value_required', ['position' => $buttonIndex]);
        }

        // Validate button type enum
        if (! empty($buttonData['button_type'])) {
            $validButtonTypes = array_column(ButtonTypeEnum::cases(), 'value');
            if (! in_array($buttonData['button_type'], $validButtonTypes)) {
                $errors['button_type'] = __('template.validation.button_type_invalid', [
                    'position' => $buttonIndex,
                    'types' => implode(', ', $validButtonTypes),
                ]);
            }
        }

        // Validate string lengths
        if (! empty($buttonData['button_text']) && strlen($buttonData['button_text']) > 255) {
            $errors['button_text'] = __('template.validation.button_text_max_length', [
                'position' => $buttonIndex,
                'max' => 255,
            ]);
        }

        if (! empty($buttonData['action_value']) && strlen($buttonData['action_value']) > 500) {
            $errors['action_value'] = __('template.validation.action_value_max_length', [
                'position' => $buttonIndex,
                'max' => 500,
            ]);
        }

        // Validate color formats
        if (! empty($buttonData['background_color']) && ! $this->isValidHexColor($buttonData['background_color'])) {
            $errors['background_color'] = __('template.validation.background_color_invalid', ['position' => $buttonIndex]);
        }

        if (! empty($buttonData['text_color']) && ! $this->isValidHexColor($buttonData['text_color'])) {
            $errors['text_color'] = __('template.validation.text_color_invalid', ['position' => $buttonIndex]);
        }

        // Type-specific validation
        if (! empty($buttonData['button_type']) && ! empty($buttonData['action_value'])) {
            try {
                $buttonTypeEnum = ButtonTypeEnum::from($buttonData['button_type']);
                $typeError = $this->validateActionValueByType($buttonTypeEnum, $buttonData['action_value'], $buttonIndex);
                if ($typeError) {
                    $errors['action_value'] = $typeError;
                }
            } catch (\ValueError $e) {
                // Button type validation already handled above
            }
        }

        return $errors;
    }

    /**
     * Validate action value based on button type
     */
    private function validateActionValueByType(ButtonTypeEnum $buttonType, string $actionValue, int $buttonIndex): ?string
    {
        return match ($buttonType) {
            ButtonTypeEnum::URL => ! filter_var($actionValue, FILTER_VALIDATE_URL)
                ? __('template.validation.action_value_invalid_url', ['position' => $buttonIndex])
                : null,

            ButtonTypeEnum::EMAIL => ! filter_var($actionValue, FILTER_VALIDATE_EMAIL)
                ? __('template.validation.action_value_invalid_email', ['position' => $buttonIndex])
                : null,

            ButtonTypeEnum::PHONE => ! preg_match('/^[\+]?[0-9\s\-\(\)]+$/', $actionValue)
                ? __('template.validation.action_value_invalid_phone', ['position' => $buttonIndex])
                : null,

            //            ButtonTypeEnum::WHATSAPP => ! preg_match('/^[\+]?[0-9]+$/', $actionValue)
            //                ? __('template.validation.action_value_invalid_whatsapp', ['position' => $buttonIndex])
            //                : null,

            ButtonTypeEnum::QUICK_REPLY => strlen($actionValue) > 20
                ? __('template.validation.action_value_quick_reply_max', ['position' => $buttonIndex, 'max' => 20])
                : null,

            default => null,
        };
    }

    /**
     * Validate hex color format
     */
    private function isValidHexColor(string $color): bool
    {
        return preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color);
    }

    /**
     * Check if template has buttons
     */
    public function hasButtons(): bool
    {
        return ! empty($this->template_buttons);
    }

    public function validateTemplateButtonData(?array $template_buttons): void
    {
        if (empty($template_buttons)) {
            return;
        }

        $this->validateAndPrepareTemplateButtons($template_buttons);
    }
}

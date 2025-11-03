<?php

namespace App\Http\Requests;

class UploadFileRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|max:102400',
        ];
    }
}

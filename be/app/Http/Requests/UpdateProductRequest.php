<?php

namespace App\Http\Requests;

use App\Enums\MarkupTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'markup_type' => ['required', new Enum(MarkupTypeEnum::class)],
            'markup_value' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
        ];
    }
}

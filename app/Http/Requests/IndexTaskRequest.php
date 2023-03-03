<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TaskStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class IndexTaskRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => Str::upper($this->status)
        ]);
    }


    public function rules(): array
    {
        return [
            'assigned-to' => [
                'string',
                'in:me,others'
            ],
            'status' => [
                'string',
                new Enum(TaskStatusEnum::class)
            ]
        ];
    }
}

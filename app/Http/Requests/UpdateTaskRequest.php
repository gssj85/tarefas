<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TaskStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'string',
                'min:3',
                'max:80',
            ],
            'description' => [
                'string',
                'min:3',
                'max:255',
            ],
            'expected_start_date' => [
                'date_format:Y-m-d H:i:s',
                'after_or_equal:' . date('Y-m-d H:i:s'),
                'before_or_equal:expected_completion_date',
            ],
            'expected_completion_date' => [
                'date_format:Y-m-d H:i:s',
                'after_or_equal:expected_start_date',
            ],
            'status' => new Enum(TaskStatusEnum::class),
            'user_id_assigned_to' => 'exists:users,id'
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'regex:/^[\p{L}\s\-]+$/u',
                'min:3',
                'max:80',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'unique:users'
            ],
            'password' => [
                'required',
                'string',
                'min:8',
            ]
        ];
    }
}

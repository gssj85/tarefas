<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\TaskStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'task' => [
                'id' => $this->id,
                'title' => $this->title,
                'description' => $this->description,
                'expected_start_date' => $this->expected_start_date->format('d/m/Y H:i'),
                'expected_completion_date' => $this->expected_completion_date->format('d/m/Y H:i:s'),
                'status' => TaskStatusEnum::from($this->status)->getTranslatedName(),
                'user' => [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                ],
                'userAssignedTo' => [
                    'id' => $this->userAssignedTo->id,
                    'name' => $this->userAssignedTo->name
                ]
            ]
        ];
    }
}

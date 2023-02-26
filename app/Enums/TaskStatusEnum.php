<?php

declare(strict_types=1);

namespace App\Enums;

enum TaskStatusEnum: string
{
    case IN_PROGRESS = 'IN_PROGRESS';
    case DONE = 'DONE';
    case CANCELED = 'CANCELED';

    public function getTranslatedName(): string
    {
        return match ($this) {
            self::IN_PROGRESS => 'Em andamento',
            self::DONE => 'Concluída',
            self::CANCELED => 'Cancelada',
        };
    }
}


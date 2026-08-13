<?php

namespace App\Enums;

enum MentoringSessionMode: string
{
    case InPerson = 'in_person';
    case Online = 'online';
    case Phone = 'phone';

    public function label(): string
    {
        return match ($this) {
            self::InPerson => 'In person',
            self::Online => 'Online',
            self::Phone => 'Phone',
        };
    }
}

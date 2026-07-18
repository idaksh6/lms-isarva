<?php

namespace App\Enums;

enum MaterialCategory: string
{
    case Syllabus = 'syllabus';
    case Notes = 'notes';
    case Dataset = 'dataset';
    case Resource = 'resource';
    case Reference = 'reference';

    public function label(): string
    {
        return match ($this) {
            self::Syllabus => 'Syllabus',
            self::Notes => 'Notes',
            self::Dataset => 'Dataset',
            self::Resource => 'Learning resource',
            self::Reference => 'Reference material',
        };
    }
}

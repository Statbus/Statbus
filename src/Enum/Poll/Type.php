<?php

namespace App\Enum\Poll;


enum Type: string
{

    case OPTION = 'OPTION';
    case TEXT = 'TEXT';
    case NUMVAL = 'NUMVAL';
    case MULTICHOICE = 'MULTICHOICE';
    case IRV = 'IRV';

    public function getIcon(): string
    {
        return match ($this) {
            Type::OPTION => 'fa-solid fa-list',
            Type::TEXT => 'fa-solid fa-i-cursor',
            Type::NUMVAL => 'fa-solid fa-list-ol',
            Type::MULTICHOICE => 'fa-solid fa-list-check',
            Type::IRV => 'fa-solid fa-check-to-slot'
        };
    }
    public function getTitle(): string
    {
        return match ($this) {
            Type::OPTION => 'Option',
            Type::TEXT => 'Text Reply',
            Type::NUMVAL => 'Numeric Rating',
            Type::MULTICHOICE => 'Multiple Choice',
            Type::IRV => 'Instant Runoff Vote',
        };
    }
}

<?php

namespace App\Enum\Election;

enum VoteType: string
{
    case PLAYER = 'Player';
    case ADMIN = 'Admin';
    case INELIGIBLE = 'Ineligible';

    public function getColor(): string
    {
        return match ($this) {
            VoteType::ADMIN => 'text-primary',
            VoteType::PLAYER => 'text-success',
            VoteType::INELIGIBLE => 'text-danger'
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            VoteType::ADMIN => 'fas fa-asterisk',
            VoteType::PLAYER => 'fas fa-circle-check',
            VoteType::INELIGIBLE => 'fas fa-circle-xmark'
        };
    }
}

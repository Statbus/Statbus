<?php

namespace App\Enum\Election;

enum VoteType: string
{
    case PLAYER = 'Player';
    case ADMIN = 'Admin';
    case INELIGIBLE = 'Ineligible';
}

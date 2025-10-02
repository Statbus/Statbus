<?php

namespace App\Enum\Election;

enum AnonymityType: string
{
    case ANONYMOUS = 'anon';
    case SEMI = 'semi';
    case NONE = 'none';

    public function getName(): string
    {
        return match ($this) {
            AnonymityType::ANONYMOUS => 'Anonymous',
            AnonymityType::SEMI => 'Semi-Anonymous ',
            AnonymityType::NONE => 'Not Anonymous'
        };
    }

    public function getDescriptor(): string
    {
        return match ($this) {
            AnonymityType::ANONYMOUS => 'Anonymous',
            AnonymityType::SEMI => 'Semi-Anonymous (reversible)',
            AnonymityType::NONE => 'Not Be Anonymous'
        };
    }

    public function getClass(): string
    {
        return match ($this) {
            AnonymityType::ANONYMOUS => 'Anonymous',
            AnonymityType::SEMI => 'Semi-Anonymous (reversible)',
            AnonymityType::NONE => 'Not Anonymous'
        };
    }
}

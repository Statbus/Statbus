<?php

namespace App\Enum;

enum PermissionFlags: int
{
    case BUILD       = (1 << 0);
    case ADMIN       = (1 << 1);
    case BAN         = (1 << 2);
    case FUN         = (1 << 3);
    case SERVER      = (1 << 4);
    case DEBUG       = (1 << 5);
    case POSSESS     = (1 << 6);
    case PERMISSIONS = (1 << 7);
    case STEALTH     = (1 << 8);
    case POLL        = (1 << 9);
    case VAREDIT     = (1 << 10);
    case SOUND       = (1 << 11);
    case SPAWN       = (1 << 12);
    case AUTOADMIN   = (1 << 13);
    case DBRANKS     = (1 << 14);

    public static function getArray(): array
    {
        foreach (self::cases() as $c) {
            $arr[$c->name] = $c->value;
        }
        return $arr;
    }
}

<?php

namespace App\Entity\Stat;

interface StatDataParserInterface
{
    public static function parseData(mixed $data): mixed;
}

<?php

namespace App\Entity\Stat;

interface StatDataParserInterface
{
    static public function parseData(mixed $data): mixed;
}

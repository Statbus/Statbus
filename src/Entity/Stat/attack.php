<?php

namespace App\Entity\Stat;

use DateTimeImmutable;

class attack implements StatDataParserInterface
{
    public static function parseData(mixed $data): mixed
    {
        unset($data[0]);
        foreach ($data as $k => &$d) {
            if (!isset($d['id'])) {
                unset($data[$k]);
                continue;
            }
            if (str_starts_with($d['msg'], '*no key*')) {
                unset($data[$k]);
                continue;
            }
            $d['ts'] = new DateTimeImmutable($d['ts']);
        }
        return $data;
    }
}

<?php

namespace App\Entity\Stat;

class qdel_log implements StatDataParserInterface
{
    public static function parseData(mixed $data): mixed
    {
        $data['keys'] = [];
        foreach ($data[1]['data'] as $d) {
            foreach ($d as $k => $v) {
                $data['keys'][$k] = null;
            }
        }
        return $data;
    }
}

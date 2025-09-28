<?php

namespace App\Entity\Stat;

class time_dilation_current3 implements StatDataParserInterface
{
    public static function parseData(mixed $data): mixed
    {
        $return = [];
        foreach ($data as $d) {
            foreach ($d as $date => $values) {
                $return[$date] = (array) $values;
            }
        }
        return $return;
    }
}

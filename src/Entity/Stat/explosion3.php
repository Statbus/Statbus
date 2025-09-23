<?php

namespace App\Entity\Stat;

use DateTimeImmutable;

class explosion3 implements StatDataParserInterface
{
    public static function parseData(mixed $data): mixed
    {
        foreach ($data as &$d) {
            $d = (object) $d;
            $d->dev = (int) $d->dev;
            $d->heavy = (int) $d->heavy;
            $d->light = (int) $d->light;
            $d->flash = (int) $d->flash;
            $d->flame = (int) $d->flame;
            $d->orig_dev = (int) $d->orig_dev;
            $d->orig_heavy = (int) $d->orig_heavy;
            $d->orig_light = (int) $d->orig_light;
            $d->x = (int) $d->x;
            $d->y = (int) $d->y;
            $d->z = (int) $d->z;
            $d->time = new DateTimeImmutable(substr($d->time, 0, -2));
            if ('*null*' == $d->possible_suspect) {
                $d->possible_suspect = null;
            }
            $d = new Explosion(
                dev: $d->dev,
                heavy: $d->heavy,
                light: $d->light,
                flash: $d->flash,
                flame: $d->flame,
                orig_dev: $d->orig_dev,
                orig_heavy: $d->orig_heavy,
                orig_light: $d->orig_light,
                x: $d->x,
                y: $d->y,
                z: $d->z,
                time: $d->time,
                area: $d->area,
                suspect: $d->possible_suspect
            );
        }

        return $data;
    }
}

class Explosion
{
    public function __construct(
        public int $dev,
        public int $heavy,
        public int $light,
        public int $flash,
        public int $flame,
        public int $orig_dev,
        public int $orig_heavy,
        public int $orig_light,
        public int $x,
        public int $y,
        public int $z,
        public DateTimeImmutable $time,
        public string $area,
        public ?string $suspect
    ) {}
}

<?php

namespace App\Entity;

use DateTimeInterface;

class Manifest
{
    public function __construct(
        public int $id,
        public int $round,
        public string $ckey,
        public string $name,
        public mixed $role,
        public mixed $special,
        public bool $lateJoin,
        public DateTimeInterface $joined
    ) {}
}

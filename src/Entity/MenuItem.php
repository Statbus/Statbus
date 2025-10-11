<?php

namespace App\Entity;

class MenuItem
{
    public function __construct(
        public string $title,
        public string $icon,
        public string $url,
        public ?string $btn = 'btn-primary',
        public ?string $img = null
    ) {}
}

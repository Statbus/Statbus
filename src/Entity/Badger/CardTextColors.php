<?php

namespace App\Entity\Badger;

use Closure;

class CardTextColors
{
    public function __construct(
        public ?string $title = null,
        public ?string $title_b = null,
        public ?string $job = null,
        public ?string $job_b = null,
        public ?string $bottom = null,
        public ?string $bottom_b = null,

        public bool $useborder = false,

        /** optional callback for text transformations (e.g. strtoupper) */
        public ?Closure $transform = null
    ) {}
}

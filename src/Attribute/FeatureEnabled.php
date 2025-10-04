<?php

namespace App\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class FeatureEnabled
{
    public function __construct(
        public string $feature
    ) {}
}

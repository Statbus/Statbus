<?php

namespace App\Twig\Extension;

use App\Service\FeatureFlagService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FeatureFlagExtension extends AbstractExtension
{
    public function __construct(
        private FeatureFlagService $flags
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('feature_enabled', [$this, 'isFeatureEnabled'])
        ];
    }

    public function isFeatureEnabled(string $flag): bool
    {
        return $this->flags->isEnabled($flag);
    }
}

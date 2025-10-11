<?php

namespace App\Service;

use App\Entity\MenuItem;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FeatureFlagService
{
    public function __construct(
        private ParameterBagInterface $params
    ) {}

    /**
     * Check if a feature is enabled.
     * Parent "enabled" keys automatically disable children.
     * Shortcut: isEnabled('deaths') → deaths.enabled (if deaths is an array)
     */
    public function isEnabled(string $path): bool
    {
        $root = $this->params->get('statbus');

        // Only append ".enabled" if the top-level path points to an array
        if (!str_contains($path, '.')) {
            if (isset($root[$path]) && is_array($root[$path])) {
                $path .= '.enabled';
            }
        }

        $segments = explode('.', $path);
        $branch = $root;
        $enabled = true;

        foreach ($segments as $segment) {
            if (!is_array($branch) || !array_key_exists($segment, $branch)) {
                // Missing flag defaults to true
                return true;
            }

            $branch = $branch[$segment];

            if (is_array($branch)) {
                $enabled = $enabled && ($branch['enabled'] ?? true);
            } else {
                $enabled = $enabled && ((bool) $branch);
            }
        }

        return $enabled;
    }

    /**
     * Return the full feature tree with resolved booleans.
     * Each child respects parent 'enabled' flags.
     */
    public function all(): array
    {
        try {
            $flags = $this->params->get('statbus');
        } catch (ParameterNotFoundException) {
            return [];
        }

        return $this->resolveBranch($flags);
    }

    private function resolveBranch(
        array $branch,
        bool $parentEnabled = true
    ): array {
        $result = [];

        foreach ($branch as $key => $value) {
            if (is_array($value)) {
                $enabled = $parentEnabled && ($value['enabled'] ?? true);
                $children = $value;
                unset($children['enabled']);
                $result[$key] = $this->resolveBranch($children, $enabled);
                $result[$key]['enabled'] = $enabled;
            } else {
                $result[$key] = $parentEnabled && ((bool) $value);
            }
        }

        return $result;
    }

    public function handleMenuItems(array $items): array
    {
        foreach ($items as $category => &$l) {
            $l = array_filter(
                $l,
                fn($key) => $this->isEnabled($key),
                ARRAY_FILTER_USE_KEY
            );
        }
        return $items;
    }
}

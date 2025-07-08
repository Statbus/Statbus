<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use BadMethodCallException;
use InvalidArgumentException;
use Twig\Extension\RuntimeExtensionInterface;

class EnumExtension implements RuntimeExtensionInterface
{
    public function createProxy(string $enumFQN): object
    {
        return new class($enumFQN) {
            public function __construct(
                private readonly string $enum
            ) {
                if (!enum_exists($this->enum)) {
                    throw new InvalidArgumentException(
                        "$this->enum is not an Enum type and cannot be used in this function"
                    );
                }
            }

            public function __call(string $name, array $arguments)
            {
                $enumFQN = sprintf('%s::%s', $this->enum, $name);

                if (defined($enumFQN)) {
                    return constant($enumFQN);
                }

                if (method_exists($this->enum, $name)) {
                    return $this->enum::$name(...$arguments);
                }

                throw new BadMethodCallException(
                    "Neither \"{$enumFQN}\" nor \"{$enumFQN}::{$name}()\" exist in this runtime."
                );
            }
        };
    }
}

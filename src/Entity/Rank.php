<?php

namespace App\Entity;

use App\Service\LuminosityContrast;

class Rank
{
    public function __construct(
        private string $name,
        private string $backColor,
        private string $icon,
        private ?string $originalRank = null
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBackColor(): string
    {
        return $this->backColor;
    }

    public function getForeColor(): string
    {
        return LuminosityContrast::getContrastColor($this->getBackColor());
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function getStyleString(): string
    {
        return sprintf(
            'background-color: %s; color: %s',
            $this->getBackColor(),
            $this->getForeColor()
        );
    }

    public static function getPlayerRank(): self
    {
        return new self('Player', '#aaa', 'fa-user');
    }

    public function setOriginalRank(?string $name): static
    {
        $this->originalRank = $name;
        return $this;
    }

    public function getOriginalRank(): ?string
    {
        return $this->originalRank;
    }
}

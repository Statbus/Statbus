<?php

namespace App\Enum\Message;

enum Severity: string
{
    case NONE = 'none';
    case MINOR = 'minor';
    case MEDIUM = 'medium';
    case HIGH = 'high';

    public function getCssClass(): string
    {
        return match ($this) {
            Severity::NONE => 'success text-white',
            Severity::MINOR => 'info',
            Severity::MEDIUM => 'warning',
            Severity::HIGH => 'danger'
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            Severity::NONE => 'fa-solid fa-circle-check',
            Severity::MINOR => 'fa-solid fa-exclamation',
            Severity::MEDIUM => 'fa-solid fa-triangle-exclamation',
            Severity::HIGH => 'fa-solid fa-circle-exclamation'
        };
    }

    public function getText(): string
    {
        return match ($this) {
            Severity::NONE => 'No Severity',
            Severity::MINOR => 'Minor Severity',
            Severity::MEDIUM => 'Medium Severity',
            Severity::HIGH => 'High Severity'
        };
    }

    public function getShortText(): string
    {
        return match ($this) {
            Severity::NONE => 'None',
            Severity::MINOR => 'Minor',
            Severity::MEDIUM => 'Medium',
            Severity::HIGH => 'High'
        };
    }

    public function altCssClass(): string
    {
        return match ($this) {
            Severity::NONE => 'success',
            Severity::MINOR => 'info',
            Severity::MEDIUM => 'warning',
            Severity::HIGH => 'danger'
        };
    }
}

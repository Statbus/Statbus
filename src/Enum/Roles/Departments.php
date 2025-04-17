<?php

namespace App\Enum\Roles;

use App\Service\LuminosityContrast;

enum Departments: string
{
    case ASSISTANT = 'Assistant';
    case SILICON = 'Silicon';
    case ENGINEERING = 'Engineering';
    case CARGO = 'Cargo';
    case SERVICE = 'Service';
    case MEDICAL = 'Medical Bay';
    case SECURITY = 'Security';
    case SCIENCE = 'Science';
    case COMMAND = 'Command';
    case ANTAG = 'Antagonist';
    case SPECIAL = 'Special';

    /**
     * getIcon
     *
     * Returns the necessary strings to compose a FontAwesome icon for this department
     *
     * @return string
     */
    public function getIcon(): string
    {
        return match ($this) {
            default => 'fa-solid fa-square-full',
            Departments::ASSISTANT => 'fa-solid fa-users-rectangle',
            Departments::SILICON => 'fa-solid fa-robot',
            Departments::ENGINEERING => 'fa-solid fa-screwdriver-wrench',
            Departments::CARGO => 'fa-solid fa-dolly',
            Departments::SERVICE => 'fa-solid fa-martini-glass-citrus',
            Departments::MEDICAL => 'fa-solid fa-heart-pulse',
            Departments::SECURITY => 'fa-solid fa-shield-halved',
            Departments::SCIENCE => 'fa-solid fa-microscope',
            Departments::COMMAND => 'fa-solid fa-person-military-pointing',
            Departments::ANTAG => 'fa-solid fa-hat-wizard',
            Departments::SPECIAL => 'fa-regular fa-snowflake'
        };
    }

    /**
     * getBackColor
     *
     * Returns an HTML color code for this department. Based off the wiki's jobs template
     *
     * @return string
     */
    public function getBackColor(): string
    {
        return match ($this) {
            Departments::ASSISTANT => '#AF6365',
            Departments::SILICON => '#B1DFF9',
            Departments::ENGINEERING => '#BA9B67',
            Departments::CARGO => '#AF763D',
            Departments::SERVICE => '#92B26D',
            Departments::MEDICAL => '#8CBCD6',
            Departments::SECURITY => '#AF6365',
            Departments::SCIENCE => '#A885A2',
            Departments::COMMAND => '#334E6D',
            Departments::ANTAG => '#000',
            Departments::SPECIAL => '#000'
        };
    }

    public function getAreaPathname(): string
    {
        return match ($this) {
            Departments::ENGINEERING => 'engineering',
            Departments::CARGO => 'cargo',
            Departments::SERVICE => 'service',
            Departments::MEDICAL => 'medical',
            Departments::SECURITY => 'security',
            Departments::SCIENCE => 'science',
            Departments::COMMAND => 'command',
            default => strtolower($this->value)
        };
    }

    /**
     * getForeColor
     *
     * Returns a black or white HTML color code depending on the color provided
     *
     * @return string
     */
    public function getForeColor(): string
    {
        return LuminosityContrast::getContrastColor($this->getBackColor());
    }

    /**
     * getStyle
     *
     * Helper method that calls getBackColor and getForeColor, and returns a string that can be applied to an element's style attribute
     *
     * @return string
     */
    public function getStyle(): string
    {
        return sprintf('background: %s; color: %s', $this->getBackColor(), $this->getForeColor());
    }

    public function getDesc(): string
    {
        return match ($this) {
            default => "A group of roles on the station",
            Departments::SPECIAL => "Special roles for time tracking"
        };
    }
}

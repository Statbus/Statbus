<?php

namespace App\Enum\Stat;

enum ThreatLevel: string
{
    case WHITE_DWARF = 'White Dwarf';
    case GREEN_STAR = 'Green Star';
    case YELLOW_STAR = 'Yellow Star';
    case ORANGE_STAR = 'Orange Star';
    case RED_STAR = 'Red Star';
    case BLACK_ORBIT = 'Black Orbit';
    case MIDNIGHT_SUN = 'Midnight Sun';

    public function getForeColor(): string
    {
        return match ($this) {
            ThreatLevel::WHITE_DWARF, ThreatLevel::YELLOW_STAR, ThreatLevel::ORANGE_STAR  => '#000',
            default => '#FFF'
        };
    }

    public function getBackColor(): string
    {
        return match ($this) {
            ThreatLevel::WHITE_DWARF => '#FFF',
            ThreatLevel::GREEN_STAR => '#146c43',
            ThreatLevel::YELLOW_STAR => '#ffcd39',
            ThreatLevel::ORANGE_STAR => '#fd7e14',
            ThreatLevel::RED_STAR => '#b02a37',
            ThreatLevel::BLACK_ORBIT => '#000',
            ThreatLevel::MIDNIGHT_SUN => '#031633'
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            ThreatLevel::WHITE_DWARF => 'fas fa-peace',
            ThreatLevel::GREEN_STAR, ThreatLevel::YELLOW_STAR, ThreatLevel::ORANGE_STAR, ThreatLevel::RED_STAR => 'fas fa-sun',
            ThreatLevel::BLACK_ORBIT => 'fas fa-satellite',
            ThreatLevel::MIDNIGHT_SUN => 'fas fa-sun'
        };
    }

    public function getStyle(): string
    {
        return sprintf("color: %s; background-color: %s", $this->getForeColor(), $this->getBackColor());
    }
}

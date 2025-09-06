<?php

namespace App\Entity\Badger;

use App\Entity\Badger\Species\Human;
use App\Entity\Badger\Species\Species;
use App\Enum\Badger\CardBackgrounds;
use App\Enum\Badger\Directions;
use App\Enum\Badger\IDCards;
use App\Factory\SpeciesFactory;

class BadgerRequest
{
    public string $speciesClassName = '';
    public ?Species $species = null;
    public string $gender;
    public Directions $direction;
    public CardBackgrounds $cardBackground;
    public IDCards $stationId;
    public string $name;
    public string $job;
    public string $bottomText;

    public string $eyeColor = '#ff0000';
    public ?string $skinTone = null;
    public string $hairColor = '#ffff00';
    public string $facialColor = '#fabe63';

    public ?string $undersuit = null;
    public ?string $ears = null;
    public ?string $mask = null;
    public ?string $helmet = null;
    public ?string $suit = null;
    public ?string $belt = null;
    public ?string $eye = null;
    public ?string $glove = null;
    public ?string $foot = null;
    public ?string $back = null;
    public ?string $neck = null;
    public ?string $hair = null;
    public ?string $facial = null;

    public ?array $hud = null;
    public ?array $augment = null;

    public ?array $holding = null;

    public ?array $mobExtras = null;

    public function __construct() {}

    public function setSpecies(Species $species): static
    {
        $this->species = $species;
        if (
            'human' === $this->species::SPRITE_PREFIX &&
                (!$this->skinTone || '#000000' === $this->skinTone)
        ) {
            $this->skinTone = Human::SKINTONES[array_rand(Human::SKINTONES)];
        }
        return $this;
    }
}

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

    public string $eyeColor = '#000000';
    public string $skinTone = '#000000';
    public string $hairColor = '#000000';
    public string $facialColor = '#000000';

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

    public mixed $hud = null;
    public mixed $augment = null;

    public ?string $rHand = null;

    public function __construct() {}

    public function setSpecies(Species $species): static
    {
        $this->species = $species;
        return $this;
    }
}

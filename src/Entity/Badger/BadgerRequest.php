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
    public ?string $underwear = null;
    public ?string $hair = null;
    public ?string $facial = null;

    public ?array $hud = null;
    public ?array $augment = null;
    public ?array $holding = null;

    public ?array $mobExtra = null;
    public ?array $behind = null;
    public ?array $front = null;

    public ?array $extras = null;
    public ?array $extraKeys = ['behindFront' => null, 'body' => null];

    public function processExtras(): void
    {
        //Elements that have a BEHIND and FRONT sprite
        foreach (array_keys(
            $this->species->extraPaths['behindFront']
        ) as $key) {
            if (array_key_exists($key, $this->extras)) {
                if (null != $this->extras[$key]) {
                    $this->front[$key] = $this->extras[$key];
                    $this->behind[$key] = $this->extras[$key];
                    $this->front[$key] = str_replace(
                        '_BEHIND',
                        '_FRONT',
                        $this->front[$key]
                    );
                    $this->behind[$key] = str_replace(
                        '_FRONT',
                        '_BEHIND',
                        $this->behind[$key]
                    );
                    $this->extraKeys['behindFront'][] = $key;
                }
            }
        }

        //Other elements that dont need to be drawn in specific order
        foreach (array_keys($this->species->extraPaths['body']) as $key) {
            if (array_key_exists($key, $this->extras)) {
                if (null != $this->extras[$key]) {
                    $this->mobExtra[$key] = $this->extras[$key];
                    $this->extraKeys['body'][] = $key;
                }
            }
        }
    }
}

<?php

namespace App\Entity\Badger;

use App\Entity\Badger\Species\Species;
use App\Enum\Badger\CardBackgrounds;
use App\Enum\Badger\Directions;
use App\Enum\Badger\IDCards;

class BadgerRequest
{
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
                    // $this->front[$key] = str_replace(
                    //     '_BEHIND',
                    //     '_FRONT',
                    //     $this->front[$key]
                    // );
                    // $this->behind[$key] = str_replace(
                    //     '_FRONT',
                    //     '_BEHIND',
                    //     $this->behind[$key]
                    // );
                    $this->extraKeys['behindFront'][] = $key;
                }
            }
        }

        //Elements that get drawn on the body
        foreach (array_keys($this->species->extraPaths['body']) as $key) {
            if (array_key_exists($key, $this->extras)) {
                if (null != $this->extras[$key]) {
                    $this->mobExtra[$key] = $this->extras[$key];
                    $this->extraKeys['body'][] = $key;
                }
            }
        }
    }

    // public function __serialize(): array
    // {
    //     return [
    //         'species' => $this->species,
    //         'gender' => $this->gender,
    //         'direction' => $this->direction->value ?? null,
    //         'cardBackground' => $this->cardBackground->value ?? null,
    //         'stationId' => $this->stationId->value ?? null,
    //         'name' => $this->name,
    //         'job' => $this->job,
    //         'bottomText' => $this->bottomText,
    //         'eyeColor' => $this->eyeColor,
    //         'skinTone' => $this->skinTone,
    //         'hairColor' => $this->hairColor,
    //         'facialColor' => $this->facialColor,
    //         'undersuit' => $this->undersuit,
    //         'ears' => $this->ears,
    //         'mask' => $this->mask,
    //         'helmet' => $this->helmet,
    //         'suit' => $this->suit,
    //         'belt' => $this->belt,
    //         'eye' => $this->eye,
    //         'glove' => $this->glove,
    //         'foot' => $this->foot,
    //         'back' => $this->back,
    //         'neck' => $this->neck,
    //         'underwear' => $this->underwear,
    //         'hair' => $this->hair,
    //         'facial' => $this->facial,
    //         'hud' => $this->hud,
    //         'augment' => $this->augment,
    //         'holding' => $this->holding,
    //         'mobExtra' => $this->mobExtra,
    //         'behind' => $this->behind,
    //         'front' => $this->front,
    //         'extras' => $this->extras,
    //         'extraKeys' => $this->extraKeys
    //     ];
    // }
}

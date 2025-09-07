<?php

namespace App\Entity\Badger;

use GdImage;

class BadgerResult
{
    public ?string $mob = null;
    public ?string $corpId = null;
    public ?string $stationId = null;

    public ?BadgerRequest $request = null;

    public function __construct() {}

    public function __serialize(): array
    {
        return [
            'mob' => $this->mob,
            'stationId' => $this->stationId,
            'corpId' => $this->corpId,
            'request' => $this->request
        ];
    }
}

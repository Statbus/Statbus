<?php

namespace App\Entity\Election;

use App\Entity\Player;
use DateTimeImmutable;

class Vote
{
    public function __construct(
        private int $id,
        private string|Player $ckey,
        private string $idBallot,
        private string $nameBallot,
        private DateTimeImmutable $cast,
        private ?string $type = null
    ) {
    }

    public function getBallotById(): string
    {
        return $this->idBallot;
    }

    public function getBallotByName(): string
    {
        return $this->nameBallot;
    }

    public function getCkey(bool $censor = false): Player|string
    {
        if ($censor) {
            return strtoupper(substr(
                hash('sha512', $_ENV['APP_SECRET'] . $this->ckey),
                0,
                6
            ));
        }
        return $this->ckey;
    }

    public function getCast(): DateTimeImmutable
    {
        return $this->cast;
    }
}

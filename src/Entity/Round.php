<?php

namespace App\Entity;

use App\Enum\Stat\ThreatLevel;
use DateTime;
use DateTimeImmutable;
use IPTools\IP;

class Round
{

    public function __construct(
        private int $id,
        private DateTimeImmutable $init,
        private ?DateTimeImmutable $start,
        private ?DateTimeImmutable $end,
        private ?DateTimeImmutable $shutdown,
        private Server $server,
        private ?string $commit,
        private ?string $result,
        private ?string $state,
        private ?string $shuttle,
        private ?string $map,
        private ?string $name,
        private ?array $threat
    ) {
        if ('undefined' === $result) {
            $this->result = null;
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getServer(): Server
    {
        return $this->server;
    }

    public function getMap(): ?string
    {
        return $this->map;
    }

    public function getInit(): DateTimeImmutable
    {
        return $this->init;
    }

    public function getStart(): ?DateTimeImmutable
    {
        return $this->start;
    }

    public function getEnd(): ?DateTimeImmutable
    {
        return $this->end;
    }

    public function getShutdown(): ?DateTimeImmutable
    {
        return $this->shutdown;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function getResult(): ?string
    {
        return $this->result;
    }

    public function getThreat(): ?array
    {
        if ($this->threat) {
            switch (true) {
                case ((int) $this->threat == 0):
                    $this->threat['badge'] = ThreatLevel::WHITE_DWARF;
                    break;
                case ((int) $this->threat < 19):
                    $this->threat['badge'] = ThreatLevel::GREEN_STAR;
                    break;
                case ((int) $this->threat < 39):
                    $this->threat['badge'] = ThreatLevel::YELLOW_STAR;
                    break;
                case ((int) $this->threat < 65):
                    $this->threat['badge'] = ThreatLevel::ORANGE_STAR;
                    break;
                case ((int) $this->threat < 79):
                    $this->threat['badge'] = ThreatLevel::RED_STAR;
                    break;
                case ((int) $this->threat < 99):
                    $this->threat['badge'] = ThreatLevel::BLACK_ORBIT;
                    break;
                case ((int) $this->threat > 100):
                    $this->threat['badge'] = ThreatLevel::MIDNIGHT_SUN;
                    break;
            }
            return $this->threat;
        }
        return null;
    }
}

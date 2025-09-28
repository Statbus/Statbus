<?php

namespace App\Entity;

use App\Enum\Stat\ThreatLevel;
use DateTimeImmutable;
use DateTimeInterface;

class Round
{
    public ?string $logUrl = null;

    public function __construct(
        public int $id,
        public DateTimeInterface $init,
        public Server $server,
        public ?DateTimeInterface $start = null,
        public ?DateTimeInterface $end = null,
        public ?DateTimeInterface $shutdown = null,
        public ?string $commit = null,
        public ?string $result = null,
        public ?string $state = null,
        public ?string $shuttle = null,
        public ?string $map = null,
        public ?string $name = null,
        public ?array $threat = null
    ) {
        if ('undefined' === $result) {
            $this->result = null;
        }
        $this->setLogUrl();
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

    public function getInit(): DateTimeInterface
    {
        return $this->init;
    }

    public function getStart(): ?DateTimeInterface
    {
        return $this->start;
    }

    public function getEnd(): ?DateTimeInterface
    {
        return $this->end;
    }

    public function getShutdown(): ?DateTimeInterface
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

    public function getCommit(bool $short = false): ?string
    {
        if ($short && $this->commit) {
            return substr($this->commit, 0, 7);
        }
        return $this->commit;
    }

    public function getThreat(): ?array
    {
        if ($this->threat) {
            switch (true) {
                case ((int) $this->threat['threat_level']) == 0:
                    $this->threat['badge'] = ThreatLevel::WHITE_DWARF;
                    break;
                case ((int) $this->threat['threat_level']) < 19:
                    $this->threat['badge'] = ThreatLevel::GREEN_STAR;
                    break;
                case ((int) $this->threat['threat_level']) < 39:
                    $this->threat['badge'] = ThreatLevel::YELLOW_STAR;
                    break;
                case ((int) $this->threat['threat_level']) < 65:
                    $this->threat['badge'] = ThreatLevel::ORANGE_STAR;
                    break;
                case ((int) $this->threat['threat_level']) < 79:
                    $this->threat['badge'] = ThreatLevel::RED_STAR;
                    break;
                case ((int) $this->threat['threat_level']) < 99:
                    $this->threat['badge'] = ThreatLevel::BLACK_ORBIT;
                    break;
                case ((int) $this->threat['threat_level']) > 100:
                    $this->threat['badge'] = ThreatLevel::MIDNIGHT_SUN;
                    break;
            }
            return $this->threat;
        }
        return null;
    }

    private function setLogUrl(): static
    {
        if ($this->getInit() < new DateTimeImmutable('2025-01-20')) {
            $this->logUrl = null;
            return $this;
        }
        $subDomain = $this->getServer()->getIdentifier() . '-logs';
        $domain = 'tgstation13.org';
        $path = sprintf(
            '%s/round-%s',
            $this->getInit()->format('Y/m/d'),
            $this->getId()
        );
        $this->logUrl = sprintf('https://%s.%s/%s', $subDomain, $domain, $path);
        return $this;
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->getId(),
            'map' => $this->getMap()
        ];
    }
}

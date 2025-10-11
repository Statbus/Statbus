<?php

namespace App\Entity;

use DateTimeImmutable;

class Stat
{
    public mixed $data;
    public ?string $parser = null;

    public function __construct(
        public int $id,
        public DateTimeImmutable $datetime,
        public int $round,
        public string $key,
        public string $type,
        public int $version,
        public string $json
    ) {
        if ('generated' === $type) {
            $this->data = json_decode($this->json, true);
        } else {
            $this->data = json_decode($this->json, true)['data'] ?? null;
        }
        $this->parseData();
    }

    public static function new($data): self
    {
        return new self(
            id: $data['id'],
            datetime: new DateTimeImmutable($data['datetime']),
            round: $data['round'],
            key: $data['key'],
            type: $data['type'],
            version: $data['version'],
            json: $data['json']
        );
    }

    public function getKey(): string
    {
        return $this->key;
    }

    private function parseData(): void
    {
        $classCandidates = [
            sprintf("\App\Entity\Stat\%s%s", $this->key, $this->version),
            sprintf("\App\Entity\Stat\%s", $this->key),
            sprintf("\App\Entity\Stat\%s", $this->type)
        ];

        foreach ($classCandidates as $class) {
            if (class_exists($class)) {
                $this->data = $class::parseData($this->data);
                $this->parser = $class;
                return;
            }
        }
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function getType(): string
    {
        return $this->type;
    }
}

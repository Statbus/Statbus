<?php

namespace App\Entity;

use DateTimeImmutable;

class Stat
{

    private mixed $data;
    private string $parser;

    public function __construct(
        private int $id,
        private DateTimeImmutable $datetime,
        private int $round,
        private string $key,
        private string $type,
        private int $version,
        private string $json,
    ) {
        $this->data = json_decode($this->json)->data ?? null;
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
            sprintf("\App\Entity\Stat\%s", $this->type),
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
}

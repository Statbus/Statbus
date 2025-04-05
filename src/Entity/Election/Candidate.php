<?php

namespace App\Entity\Election;

use DateTimeImmutable;

class Candidate
{
    public function __construct(
        private int $id,
        private string $name,
        private DateTimeImmutable $created,
        private ?string $link = null,
        private ?string $description = null,
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
}

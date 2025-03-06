<?php

namespace App\Entity;

class Option
{

    public function __construct(
        private int $id,
        private string $text,
        private int $poll,
        private ?int $min = null,
        private ?int $max = null
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getMin(): int
    {
        return $this->min;
    }

    public function getMax(): int
    {
        return $this->max;
    }
}

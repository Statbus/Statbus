<?php

namespace App\Entity;

use App\Enum\Library\Category;
use DateTimeImmutable;

class Book
{

    public function __construct(
        private int $id,
        private string $author,
        private string $title,
        private string $content,
        private Category $category,
        private Player $player,
        private DateTimeImmutable $date,
        private ?int $round
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }
}

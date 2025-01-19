<?php

namespace App\Entity;

use App\Enum\Ticket\Action;
use DateTimeImmutable;

class Ticket
{

    private bool $canBePublic = false;
    private ?string $identifier = null;

    public function __construct(
        private int $id,
        private int $round,
        private int $number,
        private int $replies,
        private string $message,
        private DateTimeImmutable $timestamp,
        private Player $sender,
        private ?Player $recipient,
        private Action $action,
        private bool $urgent,
        private Server $server,
    ) {}

    public static function new(array $data): self
    {
        return new self(
            id: $data['id'],
            round: $data['round'],
            number: $data['ticket'],
            replies: $data['replies'],
            message: $data['message'],
            timestamp: new DateTimeImmutable($data['timestamp']),
            sender: $data['sender'],
            recipient: $data['recipient'],
            action: Action::from($data['action']),
            urgent: $data['urgent'],
            server: $data['server']
        );
    }

    public function isBwoink(): bool
    {
        return $this->recipient && $this->sender && $this->action === Action::OPENED;
    }

    public function isAhelp(): bool
    {
        return !$this->recipient && $this->sender && $this->action === Action::OPENED;
    }

    public function getRound(): int
    {
        return $this->round;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getSender(): Player
    {
        return $this->sender;
    }

    public function getRecipient(): ?Player
    {
        return $this->recipient;
    }
    public function getMessage(): string
    {
        $this->message = preg_replace(
            '/(?<=\S)#(?=\S)/',
            "\n",
            $this->message
        );
        return $this->message;
    }

    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }

    public function getReplies(): int
    {
        return $this->replies;
    }

    public function getServer(): Server
    {
        return $this->server;
    }

    public function getAction(): Action
    {
        return $this->action;
    }

    public function isUrgent(): bool
    {
        return (bool) $this->urgent;
    }

    public function censor(): static
    {
        //See: https://tgstation13.org/phpBB/viewtopic.php?f=45&t=34399&p=754366#p754366
        if (str_contains($this->message, 'has created a note')) {
            $this->message = "[The contents of this message are unavailable]";
        }
        return $this;
    }

    public function setCanBePublic(bool $status): static
    {
        $this->canBePublic = $status;
        return $this;
    }

    public function canBePublic(): bool
    {
        return $this->canBePublic;
    }

    public function setIdentifier(?string $identifier): static
    {
        $this->identifier = $identifier;
        return $this;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function isPublic(): bool
    {
        return (bool) $this->identifier;
    }
}

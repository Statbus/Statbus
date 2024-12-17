<?php


namespace App\Entity;

class Server
{
    public function __construct(
        private string $name,
        private string $identifier,
        private int $port,
        private ?string $publicLogs,
        private ?string $rawLogs,
        private ?int $round
    ) {
        $this->publicLogs = str_replace(".download", ".org", $this->publicLogs);
        $this->rawLogs = str_replace(".download", ".org", $this->rawLogs);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getPublicLogs(): ?string
    {
        return $this->publicLogs;
    }

    public function getRawLogs(): ?string
    {
        return $this->rawLogs;
    }

    public function getRound(): ?int
    {
        return $this->round;
    }
}

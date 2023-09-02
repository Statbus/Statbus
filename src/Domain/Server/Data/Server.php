<?php

namespace App\Domain\Server\Data;

class Server
{
    public ?string $gameLink;

    public function __construct(
        public ?string $name,
        public ?string $address,
        public ?int $port,
        public ?string $identifier,
        public ?string $publicLogs,
        public ?string $rawlogs
    ) {
        $this->setGameServerAddress();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function setPort(?int $port): self
    {
        $this->port = $port;

        return $this;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(?string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getGameServerAddress(): string
    {
        return sprintf("byond://%s:%s", $this->getAddress(), $this->getPort());
    }

    private function setGameServerAddress(): self
    {
        $this->gameLink = $this->getGameServerAddress();
        return $this;
    }

    public function getPublicLogs(): ?string
    {
        return $this->publicLogs;
    }


    public function getAdminLogs(): ?string
    {
        return $this->rawlogs;
    }
    public static function fromArray(array $data): self
    {
        $server = new self();
        $server->setName($data['servername']);
        $server->setPort($data['port']);
        $server->setIdentifier($data['identifier']);
        $server->setAddress($data['address']);
        $server->setGameServerAddress();
        return $server;
    }
}

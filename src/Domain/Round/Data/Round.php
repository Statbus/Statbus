<?php

namespace App\Domain\Round\Data;

use App\Domain\Server\Data\Server;
use App\Enum\RoundState;
use App\Service\ServerInformationService;
use DateTime;
use JsonSerializable;

class Round implements JsonSerializable
{
    // private ?Server $server = null;

    private ?string $duration = null;

    private ?string $startDuration = null;

    private ?string $endDuration = null;

    private ?string $publicLogs = null;

    private ?string $adminLogs = null;

    public function __construct(
        private int $id,
        private ?DateTime $initDatetime = null,
        private ?DateTime $startDatetime = null,
        private ?DateTime $shutdownDatetime = null,
        private ?DateTime $endDatetime = null,
        private ?int $serverIp = null,
        private ?int $serverPort = null,
        private ?string $commit = null,
        private ?string $mode = null,
        private ?string $result = null,
        private $state = null,
        private ?string $shuttle = null,
        private ?string $map = null,
        private ?string $name = null,
        private ?Server $server = null
    ) {
        $this->setDuration();
        $this->setStartDuration();
        $this->setEndDuration();
        $this->setLogLinks();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getInitDatetime(): ?DateTime
    {
        return $this->initDatetime;
    }

    public function setInitDatetime(?DateTime $initDatetime): self
    {
        $this->initDatetime = $initDatetime;

        return $this;
    }

    public function getStartDatetime(): ?DateTime
    {
        return $this->startDatetime;
    }

    public function setStartDatetime(?DateTime $startDatetime): self
    {
        $this->startDatetime = $startDatetime;

        return $this;
    }

    public function getEndDatetime(): ?DateTime
    {
        return $this->endDatetime;
    }

    public function setEndDatetime(?DateTime $endDatetime): self
    {
        $this->endDatetime = $endDatetime;

        return $this;
    }

    public function getShutdownDatetime(): ?DateTime
    {
        return $this->shutdownDatetime;
    }

    public function setShutdownDatetime(?DateTime $shutdownDatetime): self
    {
        $this->shutdownDatetime = $shutdownDatetime;

        return $this;
    }

    public function getServerIp(): ?int
    {
        return $this->serverIp;
    }

    public function setServerIp(?int $serverIp): self
    {
        $this->serverIp = $serverIp;

        return $this;
    }

    public function getServerPort(): ?int
    {
        return $this->serverPort;
    }

    public function setServerPort(?int $serverPort): self
    {
        $this->serverPort = $serverPort;

        return $this;
    }

    public function getCommit(): ?string
    {
        return $this->commit;
    }

    public function setCommit(?string $commit): self
    {
        $this->commit = $commit;

        return $this;
    }

    public function getMode(): ?string
    {
        return $this->mode;
    }

    public function setMode(?string $mode): self
    {
        $this->mode = $mode;

        return $this;
    }

    public function getResult(): ?string
    {
        return $this->result;
    }

    public function setResult(?string $result): self
    {
        $this->result = $result;

        return $this;
    }

    public function getState(): RoundState
    {
        switch($this->state) {
            case 'proper completion':
                $state = RoundState::PROPER;
                break;
            case 'nuke':
                $state = RoundState::NUKE;
                break;
            case 'restart vote':
                $state = RoundState::VOTE;
                break;
            case 'underway':
                $state = RoundState::UNDERWAY;
                break;
            default:
                $state = RoundState::UNSUCCESSFUL;
                break;
        }
        if(str_starts_with($this->state, 'admin reboot')) {
            $state = RoundState::RESTART;
        }
        return $state;
    }

    public function setState(?string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getShuttle(): ?string
    {
        return $this->shuttle;
    }

    public function setShuttle(?string $shuttle): self
    {
        $this->shuttle = $shuttle;

        return $this;
    }

    public function getMap(): ?string
    {
        return $this->map;
    }

    public function setMap(?string $map): self
    {
        $this->map = $map;

        return $this;
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

    public function setServer(): self
    {
        return $this;
    }

    public function getServer(): ?Server
    {
        return $this->server;
    }

    public function setStartDuration(): self
    {
        if($this->getInitDatetime() && $this->getStartDatetime()) {
            $this->startDuration = $this->getStartDatetime()->diff($this->getInitDatetime())->format('%H:%I:%S');
        }
        return $this;
    }

    public function getStartDuration(): ?string
    {
        return $this->startDuration;
    }

    public function setEndDuration(): self
    {
        if($this->getEndDatetime() && $this->getShutdownDatetime()) {
            $this->endDuration = $this->getShutdownDatetime()->diff($this->getEndDatetime())->format('%H:%I:%S');
        }
        return $this;
    }

    public function getEndDuration(): ?string
    {
        return $this->endDuration;
    }

    public function setDuration(): self
    {
        if($this->getStartDatetime() && $this->getEndDatetime()) {
            $this->duration = $this->getEndDatetime()->diff($this->getStartDatetime())->format('%H:%I:%S');
        }
        return $this;
    }

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setLogLinks(): self
    {
        if($this->getServer() && $this->getStartDatetime()) {

            $server = $this->getServer();
            $date = explode(':', $this->getStartDatetime()->format('Y:m:d'));
            $logs = sprintf(
                "%sdata/logs/%s/%s/%s/round-%s",
                str_replace('.download', '.org', $server->getPublicLogs()),
                $date[0],
                $date[1],
                $date[2],
                $this->getId()
            );
            $this->setPublicLogs($logs);

            $this->setAdminLogs(str_replace('parsed-logs', 'raw-logs', $logs));
        }
        return $this;
    }

    private function setPublicLogs(string $uri): self
    {
        $this->publicLogs = $uri;
        return $this;
    }
    private function setAdminLogs(string $uri): self
    {
        $this->adminLogs = $uri;
        return $this;
    }

    public function getPublicLogs(): ?string
    {
        return $this->publicLogs;
    }

    public function getPublicLogFile(string $file): string
    {
        return sprintf("%s/%s", $this->getPublicLogs(), $file);
    }

    public function getAdminLogs(): ?string
    {
        return $this->adminLogs;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->getId(),
            'initialize_datetime' => $this->getInitDatetime(),
            'start_datetime' => $this->getStartDatetime(),
            'shutdown_datetime' => $this->getShutdownDatetime(),
            'end_datetime' => $this->getEndDatetime(),
            'server' => $this->getServer(),
            'hash' => $this->getCommit(),
            'game_mode' => $this->getMode(),
            'game_mode_result' => $this->getResult(),
            'end_state' => $this->getState(),
            'shuttle_name' => $this->getShuttle(),
            'map' => $this->getMap(),
            'name' => $this->getName()
        ];
    }
}

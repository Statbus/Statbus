<?php

namespace App\Entity;

use App\Enum\Message\Severity;
use App\Enum\Message\TypeEnum;
use DateTimeImmutable;

class Message
{

    public function __construct(
        private int $id,
        private TypeEnum $type,
        private Player $target,
        private Player $admin,
        private string $text,
        private DateTimeImmutable $timestamp,
        private Server $server,
        private ?int $round,
        private bool $secret,
        private Severity $severity,
        private ?int $playtime,
        private ?Player $editor,
        private ?array $edits,
        private ?DateTimeImmutable $expiration = null,
    ) {}

    public static function new($data): self
    {
        $target = Player::newDummyPlayer($data['targetckey'], $data['targetRank']);
        $admin = Player::newDummyPlayer($data['adminckey'], $data['adminRank']);
        $edits = null;
        if (!empty($data['edits'])) {
            $edits = explode('<hr>', $data['edits']);
            foreach ($edits as &$edit) {
                $edit = explode(' from<br>', $edit);
                $tmp['header'] = $edit[0];
                if (!empty($edit[1])) {
                    $body = explode('<br>to<br>', $edit[1]);
                    $tmp['before'] = $body[0];
                    $tmp['after'] = $body[1];
                }
                $edit = $tmp;
            }
            array_pop($edits);
        }
        return new self(
            id: $data['id'],
            type: TypeEnum::from($data['type']),
            target: $target,
            admin: $admin,
            text: $data['text'],
            timestamp: new DateTimeImmutable($data['timestamp']),
            server: $data['server'],
            round: $data['round'],
            secret: $data['secret'],
            severity: Severity::tryFrom($data['severity']) ?? Severity::NONE,
            playtime: $data['playtime'],
            editor: null,
            edits: $edits,
            expiration: (empty($data['expiration'])) ? null : new DateTimeImmutable($data['expiration'])
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): TypeEnum
    {
        return $this->type;
    }

    public function getTarget(): Player
    {
        return $this->target;
    }

    public function getAdmin(): Player
    {
        return $this->admin;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }


    public function getRound(): ?int
    {
        return $this->round;
    }

    public function isSecret(): bool
    {
        return $this->secret;
    }

    public function getSeverity(): Severity
    {
        return $this->severity;
    }

    public function getPlaytime(): int
    {
        return $this->playtime ?? 0;
    }

    public function getEditor(): Player
    {
        return $this->editor;
    }

    public function getEdits(): ?array
    {
        return $this->edits;
    }

    public function getExpiration(): ?DateTimeImmutable
    {
        return $this->expiration;
    }

    public function getServer(): Server
    {
        return $this->server;
    }

    public function isExpired(): bool
    {
        if ($this->type === TypeEnum::MEMO) {
            return false;
        }
        if (!$this->expiration) {
            return false;
        } else {
            if ($this->expiration < new DateTimeImmutable()) {
                return true;
            }
        }
        return false;
    }
}

<?php

namespace App\Entity;

use IPTools\IP;
use IPTools\Network;
use Symfony\Component\HttpFoundation\Request;

class Search
{
    public function __construct(
        private ?string $ckey = null,
        private ?int $cid = null,
        private mixed $ip = null,
        private ?string $aCkey = null,
        private ?string $text = null,
        private ?string $firstSeen = null,
        private ?string $start = null,
        private ?string $end = null,
        private ?int $connections = null,
        private ?int $minutes = null,
        private ?string $jobs = null
    ) {
    }

    private static function getRequestValue(
        Request $request,
        string $key
    ): ?string {
        $value = $request->get($key, null);
        return $value === '' ? null : $value;
    }

    public static function fromRequest(Request $request): self
    {
        $ip = self::getRequestValue($request, 'ip');
        if (!empty($ip)) {
            $ip = str_contains($ip, '/') ? Network::parse($ip) : IP::parse($ip);
        }

        return new self(
            ckey: self::getRequestValue($request, 'ckey'),
            cid: self::getRequestValue($request, 'cid'),
            ip: $ip,
            aCkey: self::getRequestValue($request, 'aCkey'),
            text: self::getRequestValue($request, 'text'),
            firstSeen: self::getRequestValue($request, 'firstSeen'),
            start: self::getRequestValue($request, 'start'),
            end: self::getRequestValue($request, 'end'),
            connections: self::getRequestValue($request, 'connections'),
            minutes: self::getRequestValue($request, 'minutes'),
            jobs: self::getRequestValue($request, 'jobs')
        );
    }

    public function isActive(): bool
    {
        return !empty(array_filter([
            $this->ckey,
            $this->cid,
            $this->ip,
            $this->aCkey,
            $this->text,
            $this->firstSeen,
            $this->start,
            $this->end,
            $this->connections,
            $this->minutes,
            $this->jobs
        ]));
    }

    public function getCkey(): ?string
    {
        return $this->ckey;
    }

    public function getCid(): ?int
    {
        return $this->cid;
    }

    public function getIp(): mixed
    {
        return $this->ip;
    }

    public function getACkey(): ?string
    {
        return $this->aCkey;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function getFirstSeen(): ?string
    {
        return $this->firstSeen;
    }

    public function getConnections(): ?int
    {
        return $this->connections;
    }
}

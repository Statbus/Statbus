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
        private ?string $text = null
    )
    {
        
    }

    public static function fromRequest(Request $request): self {
        $ip = $request->get('ip', null);
        if ($ip === "") {
            $ip = null;
        }
        if ($ip) {
            if (str_contains($ip, '/')) {
                $ip = Network::parse($ip);
            } else {
                $ip = IP::parse($ip);
            }
        }
        $cid = $request->get('cid');
        if ($cid === "") {
            $cid = null;
        }

        $ckey = $request->get('ckey');
        if ($ckey === "") {
            $ckey = null;
        }

        $aCkey = $request->get('aCkey');
        if ($aCkey === "") {
            $aCkey = null;
        }
        $text = $request->get('text');
        if ($text === "") {
            $text = null;
        }
        return new self(
            $ckey,
            $cid,
            $ip,
            $aCkey,
            $text
        );
    }

    public function isActive(): bool {
        return $this->ckey || $this->cid || $this->ip || $this->aCkey || $this->text;
    }

    public function getCkey(): ?string {
        return $this->ckey;
    }
    
    public function getCid(): ?int {
        return $this->cid;
    }

    public function getIp(): mixed {
        return $this->ip;
    }

    public function getACkey(): ?string {
        return $this->aCkey;
    }

    public function getText(): ?string {
        return $this->text;
    }

}

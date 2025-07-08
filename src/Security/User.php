<?php

namespace App\Security;

use App\Entity\Rank;
use App\Enum\PermissionFlags;
use App\Repository\AllowListEntry;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    private $roles = [];

    public function __construct(
        private string $ckey,
        private ?int $flags = 0,
        private ?Rank $rank = null,
        private ?AllowListEntry $allowList = null,
        private ?string $feedback = null,
        private ?array $extraRoles = null
    ) {
        $this->generateRoles();
        if (!$this->rank) {
            $this->rank = Rank::getPlayerRank();
        }
    }

    public static function new(
        string $ckey,
        ?int $flags = 0,
        ?Rank $rank = null,
        ?AllowListEntry $list = null,
        ?string $feedback = null,
        ?array $extraRoles = null
    ) {
        if ($list) {
            $flags = $flags += PermissionFlags::BAN->value;
        }
        $user = new static(
            ckey: $ckey,
            flags: $flags,
            rank: $rank,
            allowList: $list,
            feedback: $feedback,
            extraRoles: $extraRoles
        );

        return $user;
    }

    public function getCkey(): ?string
    {
        return $this->ckey;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->ckey;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function generateRoles(): static
    {
        foreach (PermissionFlags::getArray() as $p => $b) {
            if ($this->getFlags() & $b) {
                $this->roles[] = 'ROLE_' . $p;
            }
        }
        if ($this->allowList) {
            $this->roles[] = 'ROLE_TEMPORARY';
        }
        if ($this->extraRoles) {
            $this->roles = [...$this->roles, ...$this->extraRoles];
        }
        return $this;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles);
    }

    public function eraseCredentials(): void
    {
    }

    public function getRank(): Rank
    {
        return $this->rank;
    }

    public function getFlags(): ?int
    {
        return $this->flags;
    }

    // public function isEqualTo(UserInterface $user): bool
    // {
    //     return $this->getCkey() === $user->getCkey()
    //         && $this->rank->getName() === $user->getRank()->getName();
    // }

    public function getAllowListEntry(): ?AllowListEntry
    {
        return $this->allowList;
    }

    public function getFeedbackUri(): ?string
    {
        return $this->feedback;
    }
}

<?php

namespace App\Security;

use App\Entity\Rank;
use App\Enum\PermissionFlags;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface, EquatableInterface
{

    private $roles = [];

    public function __construct(
        private string $ckey,
        private ?int $flags = 0,
        private ?Rank $rank = null
    ) {
        $this->generateRoles();
        if (!$this->rank) {
            $this->rank = new Rank('Player', '#aaa', 'fa6-solid:user');
        }
    }

    public static function new(
        string $ckey,
        ?int $flags = 0,
        ?Rank $rank = null
    ) {
        $user = new static(
            ckey: $ckey,
            flags: $flags,
            rank: $rank
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
                $this->roles[] = "ROLE_" . $p;
            }
        }
        return $this;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles);
    }

    public function eraseCredentials(): void {}

    public function getRank(): Rank
    {
        return $this->rank;
    }

    public function getFlags(): ?int
    {
        return $this->flags;
    }

    public function isEqualTo(UserInterface $user): bool
    {
        return $this->getCkey() === $user->getCkey()
            && $this->rank->getName() === $user->getRank()->getName();
    }
}

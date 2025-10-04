<?php

namespace App\Service;

use App\Repository\AllowListEntry;
use App\Repository\AllowListRepository;
use App\Repository\PlayerRepository;
use App\Security\User;
use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Exception;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class AllowListService
{
    public const EXPIRATIONS = [
        1,
        6,
        12,
        24
    ];

    public function __construct(
        private PlayerRepository $playerRepository,
        private AllowListRepository $allowListRepository,
        private FeatureFlagService $feature
    ) {}

    public function addCkeyToAllowList(
        string $ckey,
        User $admin,
        int $expiration,
        string $reason
    ) {
        if (!in_array($expiration, static::EXPIRATIONS)) {
            throw new BadRequestException('Invalid expiration time');
        }
        if ('' === $reason || empty($reason)) {
            throw new BadRequestException('A reason is required');
        }
        $expiration = (new DateTimeImmutable(
            'now',
            new DateTimeZone('UTC')
        ))->add(new DateInterval('PT' . $expiration . 'H'));
        $target = $this->playerRepository->findByCkey($ckey, true);
        if (!$target) {
            throw new BadRequestException('This ckey does not exist');
        }
        try {
            $this->allowListRepository->insertNewEntry(
                $target,
                $admin,
                $reason,
                $expiration
            );
        } catch (UniqueConstraintViolationException $e) {
            throw new BadRequestException(
                'This ckey is already on the allow list'
            );
        }
    }

    public function revokeEntry(int $id, User $user): void
    {
        $this->allowListRepository->markEntryRevoked($id, $user);
    }

    public function getActiveList(): array
    {
        return $this->allowListRepository->getList();
    }

    public function isUserOnAllowList(User|string $user): ?AllowListEntry
    {
        if (!$this->feature->isEnabled('allowList')) {
            return null;
        }
        return $this->allowListRepository->findUser($user);
    }
}

<?php

namespace App\Repository;

use App\Entity\Player;
use App\Entity\Rank;
use App\Security\User;
use DateTimeImmutable;

class AllowListRepository extends StatbusRepository
{

    public const ENTITY = AllowListEntry::class;

    public function insertNewEntry(
        Player $player,
        User $addedBy,
        string $reason,
        DateTimeImmutable $expiration
    ) {
        $qb = $this->qb();
        $qb->insert('allow_list')
            ->values([
                'ckey' => $qb->createNamedParameter($player->getCkey()),
                'admin' => $qb->createNamedParameter($addedBy->getCkey()),
                'adminrank' => $qb->createNamedParameter($addedBy->getRank()->getName()),
                'reason' => $qb->createNamedParameter($reason),
                'expiration' => $qb->createNamedParameter($expiration->format('Y-m-d H:i:s'))
            ])->executeStatement();
    }

    public function getList(): array
    {
        $qb = $this->qb();
        $qb
            ->select(
                'l.id',
                'l.ckey',
                'l.admin',
                'l.adminrank',
                'l.datetime',
                'l.expiration',
                'l.reason'
            )
            ->where('l.expiration > NOW()')
            ->andWhere('l.revoked IS NULL')
            ->from('allow_list', 'l');
        // ->leftJoin('l', 'admin', 'r', 'r.ckey = l.addedBy');
        $results = $qb->executeQuery()->fetchAllAssociative();
        foreach ($results as &$r) {
            $r['adminrank'] = $this->rankService->getRankByName($r['adminrank']);
            $r = $this->parseRow($r);
        }
        return $results;
    }

    public function findUser(User|string $user): ?AllowListEntry
    {
        if ($user instanceof User) {
            $user = $user->getCkey();
        }
        $qb = $this->qb();
        $qb
            ->select(
                'l.id',
                'l.ckey',
                'l.admin',
                'l.adminrank',
                'l.datetime',
                'l.expiration',
                'l.reason'
            )
            ->from('allow_list', 'l')
            ->where('l.expiration > NOW()')
            ->andWhere('l.revoked IS NULL')
            ->andWhere('l.ckey = ' . $qb->createNamedParameter($user));
        $result = $qb->executeQuery()->fetchAssociative();
        if (!$result) {
            return null;
        }
        $result['adminrank'] = $this->rankService->getRankByName($result['adminrank']);
        return $this->parseRow($result);
    }

    public function markEntryRevoked(int $id, User $user): void
    {
        $qb = $this->qb();
        $qb->update('allow_list', 'l')
            ->set(
                'l.revoked',
                $qb->createNamedParameter($user->getCkey())
            )->where('l.id = ' . $qb->createNamedParameter($id));
        $qb->executeStatement();
    }
}

class AllowListEntry
{
    public function __construct(
        public int $id,
        public Player $player,
        public Player $admin,
        public DateTimeImmutable $created,
        public DateTimeImmutable $expiration,
        public string $reason
    ) {}

    public static function new(array $r): self
    {
        $player = Player::newDummyPlayer($r['ckey'], Rank::getPlayerRank());
        $admin = Player::newDummyPlayer($r['admin'], $r['adminrank']);
        return new self(
            id: $r['id'],
            player: $player,
            admin: $admin,
            created: new DateTimeImmutable($r['datetime']),
            expiration: new DateTimeImmutable($r['expiration']),
            reason: $r['reason']
        );
    }
}

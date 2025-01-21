<?php

namespace App\Repository;

use App\Entity\Player;
use App\Entity\Rank;
use App\Security\User;
use App\Service\RankService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

class PlayerRepository extends ServiceEntityRepository
{
    public function __construct(
        private ManagerRegistry $registry,
        private Connection $connection,
        private RankService $rankService
    ) {
        parent::__construct($registry, User::class);
    }

    public function findByCkey(string $ckey): Player
    {
        $livingQuery = $this->connection->createQueryBuilder()
            ->select('minutes')
            ->from('role_time')
            ->where("job = 'Living'")
            ->andWhere('ckey = :ckey')
            ->getSQL();

        $ghostQuery = $this->connection->createQueryBuilder()
            ->select('minutes')
            ->from('role_time')
            ->where("job = 'Ghost'")
            ->andWhere('ckey = :ckey')
            ->getSQL();

        $qb = $this->connection->createQueryBuilder();
        $qb->from('player', 'p')
            ->select(
                'p.ckey',
                'a.rank',
                'r.flags',
                'p.firstseen as firstSeen',
                'p.lastseen as lastSeen',
                'p.accountjoindate as accountJoinDate',
                'p.ip',
                'p.computerid as cid',
                "($livingQuery) AS living",
                "($ghostQuery) AS ghost"
            )
            ->leftJoin('p', 'admin', 'a', 'p.ckey = a.ckey')
            ->leftJoin('p', 'admin_ranks', 'r', 'r.rank = a.rank')
            ->where('p.ckey = :ckey')
            ->setParameter('ckey', $ckey);

        $player = $qb->executeQuery()->fetchAssociative();

        try {
            $player['rank'] = $this->rankService->getRankByName($player['rank']);
        } catch (Exception $e) {
            $player['rank'] = Rank::getPlayerRank();
        }
        $player['living'] = $player['living'] ?? 0;
        $player['ghost'] = $player['ghost'] ?? 0;
        return Player::newPlayer(...$player);
    }
}

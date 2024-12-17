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
        $qb = $this->connection->createQueryBuilder();
        $player = $qb->from('player', 'p')
            ->select(
                'p.ckey',
                'a.rank',
                'r.flags',
                'p.firstseen as firstSeen',
                'p.lastseen as lastSeen',
                'p.accountjoindate as accountJoinDate',
                'p.ip',
                'p.computerid as cid'
            )
            ->leftJoin('p', 'admin', 'a', 'p.ckey = a.ckey')
            ->leftJoin('p', 'admin_ranks', 'r', 'r.rank = a.rank')
            ->where('p.ckey = ' . $qb->createNamedParameter($ckey))
            ->executeQuery()->fetchAssociative();
        try {
            $player['rank'] = $this->rankService->getRankByName($player['rank']);
        } catch (Exception $e) {
            $player['rank'] = Rank::getPlayerRank();
        }
        return Player::newPlayer(...$player);
    }
}

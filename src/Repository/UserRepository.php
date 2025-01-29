<?php

namespace App\Repository;

use App\Entity\Rank;
use App\Security\User;
use App\Service\RankService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(
        private ManagerRegistry $registry,
        private Connection $connection,
        private RankService $rankService
    ) {
        parent::__construct($registry, User::class);
    }

    public function findByCkey(string $ckey, bool $overrideFlags = false): User
    {
        $qb = $this->connection->createQueryBuilder();
        $user = $qb->from('player', 'p')
            ->select(
                'p.ckey',
                'a.rank',
                'r.flags'
            )
            ->leftJoin('p', 'admin', 'a', 'p.ckey = a.ckey')
            ->leftJoin('p', 'admin_ranks', 'r', 'r.rank = a.rank')
            ->where('p.ckey = ' . $qb->createNamedParameter($ckey))
            ->executeQuery()->fetchAssociative();
        try {
            $user['rank'] = $this->rankService->getRanks()[$user['rank']];
        } catch (Exception $e) {
            $user['rank'] = new Rank('Player', '#aaa', 'fa-user');
        }
        if ($overrideFlags) {
            $user['flags'] = 0;
        }
        return User::new(...$user);
    }
}

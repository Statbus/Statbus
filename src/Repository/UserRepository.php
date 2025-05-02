<?php

namespace App\Repository;

use App\Entity\Rank;
use App\Enum\PermissionFlags;
use App\Security\User;
use App\Service\AllowListService;
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
        private RankService $rankService,
        private AllowListService $allowListService,
        private array $electionOfficers
    ) {
        parent::__construct($registry, User::class);
    }

    public function findByCkey(string $ckey): User
    {
        $qb = $this->connection->createQueryBuilder();
        $user = $qb->from('player', 'p')
            ->select(
                'p.ckey',
                "SUBSTRING_INDEX(SUBSTRING_INDEX(a.rank, '+', 1), ',', -1) as rank",
                "(SELECT r.flags FROM admin_ranks r WHERE rank = SUBSTRING_INDEX(SUBSTRING_INDEX(a.rank, '+', 1), ',', -1)) as flags",
                'a.feedback'
            )
            ->leftJoin('p', 'admin', 'a', 'p.ckey = a.ckey')
            ->leftJoin('p', 'admin_ranks', 'r', 'r.rank = a.rank')
            ->where('p.ckey = ' . $qb->createNamedParameter($ckey))
            ->executeQuery()->fetchAssociative();
        try {
            $user['rank'] = $this->rankService->getRanks()[$user['rank']];
        } catch (Exception $e) {
            $user['rank'] = Rank::getPlayerRank();
        }
        if ($list = $this->allowListService->isUserOnAllowList($user['ckey'])) {
            $user['list'] = $list;
        }
        if (in_array($user['ckey'], $this->electionOfficers)) {
            $user['extraRoles'] = ['ROLE_ELECTION'];
        }
        return User::new(...$user);
    }
}

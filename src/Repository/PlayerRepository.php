<?php

namespace App\Repository;

use App\Entity\Player;
use App\Entity\Rank;
use App\Enum\Roles\Jobs;
use App\Security\User;
use App\Service\Player\GetBasicPlayerService;
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
        private RankService $rankService,
        private GetBasicPlayerService $playerService,
    ) {
        parent::__construct($registry, User::class);
    }

    public function findByCkey(string $ckey, bool $short = false): Player
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
                "SUBSTRING_INDEX(SUBSTRING_INDEX(a.rank, '+', 1), ',', -1) as rank",
                "(SELECT r.flags FROM admin_ranks r WHERE rank = SUBSTRING_INDEX(SUBSTRING_INDEX(a.rank, '+', 1), ',', -1)) as flags",
                'p.firstseen as firstSeen',
                'p.lastseen as lastSeen',
                'p.accountjoindate as accountJoinDate',
                'p.ip',
                'p.computerid as cid'
            );
        if (!$short) {
            $qb->addSelect(
                "($livingQuery) AS living",
                "($ghostQuery) AS ghost",
                "count(distinct c.round_id) AS rounds",
                "count(distinct d.id) AS deaths"
            )
                ->leftJoin('p', 'connection_log', 'c', 'c.ckey = p.ckey')
                ->leftJoin('p', 'death', 'd', 'd.byondkey = p.ckey');
        }
        $qb
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

    public function getRecentPlayerRounds(string $ckey): array
    {
        $sql = "
        WITH RECURSIVE date_series AS (
            SELECT CURDATE() - INTERVAL 30 DAY AS day
            UNION ALL
            SELECT day + INTERVAL 1 DAY FROM date_series WHERE day < CURDATE()
        )
        SELECT 
            ds.day, 
            COALESCE(COUNT(DISTINCT c.round_id), 0) AS rounds
        FROM date_series ds
        LEFT JOIN connection_log c 
            ON DATE(c.datetime) = ds.day 
            AND c.datetime > NOW() - INTERVAL 30 DAY
            AND c.ckey = :ckey
        GROUP BY ds.day
        ORDER BY ds.day;
        ";
        $result = $this->connection->executeQuery($sql, ['ckey' => $ckey]);
        return $result->fetchAllKeyValue();
    }

    public function search(string $ckey): array
    {
        $qb = $this->connection->createQueryBuilder();
        $result = $qb->select('ckey')
            ->from('player')
            ->where($qb->expr()->like('ckey', ':ckey'))
            ->setParameter('ckey', "%$ckey%")
            ->orderBy('lastseen', 'DESC')
            ->executeQuery();
        return $result->fetchAllAssociative();
    }

    public function getPlayerRecentPlaytime(string $ckey): array
    {
        $list = [];
        foreach (Jobs::cases() as $job) {
            if ($job->includeInGraph()) {
                $list[] = $job->value;
            }
        }
        $jobs = "('" . implode("','", $list) . "')";
        $qb = $this->connection->createQueryBuilder();
        $results = $qb->select(
            'sum(t.delta) as `minutes`',
            't.job'
        )->from('role_time_log', 't')
            ->where($qb->expr()->eq('t.ckey', ':ckey'))
            ->andWhere('t.job in ' . $jobs)
            ->andWhere('t.datetime BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE()')
            ->groupBy('t.job')
            ->orderBy('`minutes`', 'DESC')
            ->setParameter('ckey', $ckey)->executeQuery()->fetchAllAssociative();
        foreach ($results as &$d) {
            $job = Jobs::tryFrom($d['job']);
            if (!$job) {
                continue;
            }
            $d['minutes'] = (int) $d['minutes'] + (rand(1, 3) * 10);
            $d['background'] = $job->getColor();
        }
        return $results;
    }

    public function getKnownAlts(Player $player): array
    {
        $qb = $this->connection->createQueryBuilder();
        $results = $qb->select('k.ckey2 as alt', 'k.admin_ckey as admin', 'a.rank')
            ->from('known_alts', 'k')
            ->leftJoin('k', 'admin', 'a', 'k.admin_ckey = a.ckey')
            ->where('k.ckey1 = ' . $qb->createNamedParameter($player->getCkey()))
            ->executeQuery()->fetchAllAssociative();
        foreach ($results as &$r) {
            $r['admin'] = $this->playerService->playerFromCkey($r['admin'], $r['rank']);
        }
        return $results;
    }

    public function getNewPlayers(): array
    {
        $query = "SELECT player.ckey, player.firstseen, player.lastseen, player.accountjoindate,
        (SELECT GROUP_CONCAT(DISTINCT ckey) FROM connection_log AS dupe WHERE dupe.datetime BETWEEN player.firstseen - INTERVAL 3 DAY AND player.firstseen AND dupe.computerid IN (SELECT DISTINCT connection_log.computerid FROM connection_log WHERE connection_log.ckey = player.ckey) AND dupe.ckey != player.ckey) AS cid_recent_connection_matches,

        (SELECT GROUP_CONCAT(DISTINCT ckey) FROM connection_log AS dupe WHERE dupe.datetime BETWEEN player.firstseen - INTERVAL 3 DAY AND player.firstseen AND dupe.ip IN (select DISTINCT connection_log.ip FROM connection_log WHERE connection_log.ckey = player.ckey) AND dupe.ckey != player.ckey) AS ip_recent_connection_matches,

        (SELECT GROUP_CONCAT(DISTINCT ckey) FROM player AS dupe WHERE dupe.computerid IN (SELECT DISTINCT connection_log.computerid FROM connection_log WHERE connection_log.ckey = player.ckey) AND dupe.ckey != player.ckey) AS cid_last_connection_matches,

        (SELECT GROUP_CONCAT(DISTINCT ckey) FROM player AS dupe WHERE dupe.ip IN (select DISTINCT connection_log.ip FROM connection_log WHERE connection_log.ckey = player.ckey) AND dupe.ckey != player.ckey) AS ip_last_connection_matches

        FROM player
        WHERE player.firstseen > NOW() - INTERVAL 3 DAY
        GROUP BY player.ckey
        ORDER BY player.firstseen DESC;";
        $data = $this->connection->executeQuery($query)->fetchAllAssociative();
        foreach ($data as &$d) {
            $d['cid_recent_connection_matches'] = explode(',', $d['cid_recent_connection_matches']);
            $d['ip_recent_connection_matches'] = explode(',', $d['ip_recent_connection_matches']);
            $d['cid_last_connection_matches'] = explode(',', $d['cid_last_connection_matches']);
            $d['ip_last_connection_matches'] = explode(',', $d['ip_last_connection_matches']);
        }
        return $data;
    }
}

<?php

namespace App\Repository;

use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Query\QueryBuilder;
use IPTools\IP;
use IPTools\Network;

class ConnectionRepository extends TGRepository
{
    public const TABLE = 'connection_log';
    public const ALIAS = 'c';

    public function getBaseQuery(): QueryBuilder
    {
        $qb = $this->qb();
        $qb
            ->select(
                'DATE(datetime) AS `day`',
                'ckey',
                'ip',
                'computerid',
                'count(id) as count',
                'server_ip',
                'server_port'
            )
            ->from('connection_log')
            ->groupBy(
                'day',
                'server_ip',
                'server_port',
                'ckey',
                'ip',
                'computerid'
            )
            ->orderBy('day', 'DESC')
            ->setMaxResults(1000);
        return $qb;
    }

    public function findConnections(?string $ckey, mixed $ip, ?int $cid): array
    {
        $qb = $this->getBaseQuery();
        if ($ckey) {
            $qb->orWhere('ckey LIKE :ckey')->setParameter('ckey', $ckey . '%');
        }
        if ($cid) {
            $qb->orWhere('computerid LIKE :cid')->setParameter('cid', $cid);
        }
        if ($ip) {
            if ($ip instanceof Network) {
                $qb->orWhere('ip BETWEEN :start AND :end')->setParameter(
                    'start',
                    (new IP($ip->getFirstIP()))->toLong()
                )->setParameter('end', (new IP($ip->getLastIP()))->toLong());
            } else {
                $qb->orWhere('ip = :ip')->setParameter('ip', $ip->toLong());
            }
        }
        $result = $qb->executeQuery();
        $this->query = $qb->getSQL();
        $this->params = $qb->getParameters();
        return $result->fetchAllAssociative();
    }

    public function fetchConnectionYearRange(): array
    {
        $qb = $this->qb();
        $qb
            ->enableResultCache(new QueryCacheProfile(86400))
            ->select('YEAR(c.datetime)')
            ->distinct()
            ->from(static::TABLE, static::ALIAS)
            ->executeQuery();
        return $qb->fetchFirstColumn();
    }

    public function fetchRecentConnectionCounts(
        string $key,
        int|string $value
    ): array {
        $qb = $this->qb();
        $qb->select(
            'count(DISTINCT c.ckey) as players',
            // 'count(distinct c.round_id) as rounds',
            'DATE_FORMAT(c.datetime, "%Y-%m-%d") as `date`',
            'c.server_port as port'
        )->from('connection_log', 'c');
        switch ($key):
            case 'year':
                if (((int) $key) === (new DateTimeImmutable())->format('Y')) {
                    $qb->enableResultCache(new QueryCacheProfile(86400));
                } else {
                    $qb->enableResultCache(new QueryCacheProfile(-1));
                }
                $qb->where('YEAR(c.datetime) = ' .
                    $qb->createNamedParameter($value));
                $end = new DateTimeImmutable($value . '-12-31');
                $start = $end->sub(new DateInterval('P1Y'));
                $interval = new DateInterval('P1D');
                $period = new DatePeriod($start, $interval, $end);
                break;
            case 'days':
                $qb->where('c.datetime BETWEEN CURDATE() - INTERVAL ' .
                $qb->createNamedParameter($value) .
                    ' DAY AND CURDATE()');
                $end = new DateTimeImmutable();
                $start = $end->sub(new DateInterval(sprintf('P%sD', $value)));
                $interval = new DateInterval('P1D');
                $period = new DatePeriod($start, $interval, $end);
                break;
        endswitch;
        $qb->groupBy(
            'c.server_port',
            'YEAR(c.datetime)',
            'MONTH(c.datetime)',
            'DAY(c.datetime)'
        )->orderBy('c.datetime', 'DESC');
        $results = $qb->executeQuery()->fetchAllAssociative();
        $fullDates = [];
        foreach ($period as $date) {
            $fullDates[$date->format('Y-m-d')] = [];
        }
        foreach ($results as $r) {
            $server = $this->serverInformationService
                ->getServerFromPort($r['port'])
                ->getIdentifier();

            $fullDates[$r['date']][$server] = $r['players'];
        }
        return $fullDates;
    }
}

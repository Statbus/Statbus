<?php

namespace App\Repository;

use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use Doctrine\DBAL\Cache\QueryCacheProfile;

class PopulationRepository extends TGRepository
{
    public function fetchPopulationYearRange(): array
    {
        $qb = $this->qb();
        $qb
            ->enableResultCache(new QueryCacheProfile(86400))
            ->select('distinct YEAR(`time`)')
            ->distinct()
            ->from('legacy_population')
            ->executeQuery();
        return $qb->fetchFirstColumn();
    }

    public function getYearlyChartData(int $year, string $method): array
    {
        $qb = $this->qb();
        switch ($method) {
            case 'avg':
            default:
                $qb->select(
                    'avg(playercount) as players',
                    'avg(admincount) as admins',
                    'DATE_FORMAT(time, "%Y-%m-%d") as `date`',
                    'server_ip',
                    'server_port as port'
                );
                break;
            case 'max':
                $qb->select(
                    'max(playercount) as players',
                    'max(admincount) as admins',
                    'DATE_FORMAT(time, "%Y-%m-%d") as `date`',
                    'server_ip',
                    'server_port as port'
                );
                break;
        }

        $qb
            ->from('legacy_population')
            ->where('YEAR(`time`) = ' . $qb->createNamedParameter($year))
            ->groupBy('port', 'YEAR(`time`)', 'MONTH(`time`)', 'DAY(`time`)')
            ->orderBy('`time`', 'DESC');
        $rows = $qb->executeQuery()->fetchAllAssociative();
        $start = new DateTimeImmutable($year . '-01-01 00:00:00');
        if ($year != (new DateTimeImmutable())->format('Y')) {
            $end = new DateTimeImmutable($year . '-12-31 23:59:59');
        } else {
            $end = (new DateTimeImmutable('yesterday'))->setTime(23, 59, 59);
        }
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($start, $interval, $end);
        foreach ($period as $date) {
            $dates[$date->format('Y-m-d')] = [];
        }
        foreach ($rows as &$r) {
            $r['server'] = $this->serverInformationService->getServerFromPort(
                port: $r['port'],
                useCached: true
            );
            $dates[$r['date']][$r['server']->getIdentifier()] = [
                'players' => (float) $r['players'],
                'admins' => (float) $r['admins']
            ];
        }
        return $dates;
    }

    public function getHourlyChartData(): array
    {
        $qb = $this->qb();
        $qb
            ->select(
                'avg(playercount) as players',
                'avg(admincount) as admins',
                'DATE_FORMAT(`time`, "%H") as hour',
                'server_ip',
                'server_port as port'
            )
            ->from('legacy_population')
            ->where('`time` BETWEEN NOW() - INTERVAL 30 DAY AND NOW()')
            ->groupBy('port', 'HOUR(`time`)')
            ->orderBy('HOUR(`time`)', 'DESC');
        $rows = $qb->executeQuery()->fetchAllAssociative();
        $hours = [];
        foreach ($rows as &$r) {
            $r['server'] = $this->serverInformationService->getServerFromPort(
                port: $r['port'],
                useCached: true
            );
            $hours[$r['hour']][$r['server']->getIdentifier()] = [
                'players' => (float) $r['players'],
                'admins' => (float) $r['admins']
            ];
        }
        return $hours;
    }
}

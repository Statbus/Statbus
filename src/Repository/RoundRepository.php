<?php

namespace App\Repository;

use App\Entity\Manifest;
use App\Entity\Round;
use App\Enum\Roles\Jobs;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use Knp\Component\Pager\Pagination\PaginationInterface;

class RoundRepository extends TGRepository
{
    public const PER_PAGE = 60;
    public const COLUMNS = [
        'r.id',
        'r.initialize_datetime as init',
        'r.start_datetime as start',
        'r.shutdown_datetime as shutdown',
        'r.end_datetime as end',
        'r.server_ip',
        'r.server_port',
        'r.commit_hash as commit',
        'r.game_mode as mode',
        'r.game_mode_result as result',
        'r.end_state as state',
        'r.map_name as map'
    ];
    public const TABLE = 'round';
    public const ALIAS = 'r';
    public const ENTITY = Round::class;
    public const ORDERBY = 'r.id';

    public function getRounds(int $page): PaginationInterface
    {
        $query = $this->getBaseQuery();
        $pagination = $this->paginatorInterface->paginate(
            $query,
            $page,
            static::PER_PAGE
        );
        $tmp = [];
        foreach ($pagination->getItems() as $r) {
            $tmp[] = $this->parseRow($r);
        }
        $pagination->setItems($tmp);
        return $pagination;
    }

    public function fetchRoundsForCkey(
        string $ckey,
        int $page
    ): PaginationInterface {
        $currentRounds = $this->serverInformationService->getCurrentRounds();
        $qb = $this->qb();

        $connSub = $this->qb();
        $connSub
            ->select('c.round_id, MAX(c.datetime) AS connect_datetime')
            ->from('connection_log', 'c')
            ->andWhere('c.ckey = ' . $connSub->createNamedParameter($ckey))
            ->groupBy('c.round_id');

        $qb
            ->select(array_merge(static::COLUMNS, [
                'm.job',
                'm.timestamp as joined',
                'm.special',
                'cl.connect_datetime'
            ]))
            ->from('round', 'r')
            ->innerJoin(
                'r',
                '(' . $connSub->getSQL() . ')',
                'cl',
                'cl.round_id = r.id'
            )
            ->leftJoin(
                'r',
                'manifest',
                'm',
                'm.round_id = r.id AND m.ckey = ' .
                    $qb->createNamedParameter($ckey)
            )
            ->andWhere('r.id IS NOT NULL')
            ->orderBy('r.id', 'DESC');

        if ($currentRounds) {
            $qb->andWhere('r.id NOT IN (' . implode(',', $currentRounds) . ')');
        }

        $pagination = $this->paginatorInterface->paginate(
            $qb,
            $page,
            static::PER_PAGE
        );

        $tmp = [];
        foreach ($pagination->getItems() as $r) {
            if ($r['job']) {
                $manifest = new Manifest(
                    id: -1,
                    round: $r['id'],
                    ckey: $ckey,
                    name: 'Null',
                    role: Jobs::tryFrom($r['job']),
                    special: Jobs::tryFrom($r['special']) ?? null,
                    lateJoin: false,
                    joined: new DateTimeImmutable($r['joined'])
                );
            } else {
                $manifest = new Manifest(
                    id: -1,
                    round: $r['id'],
                    ckey: $ckey,
                    name: 'Null',
                    role: Jobs::OBSERVER,
                    special: null,
                    lateJoin: false,
                    joined: new DateTimeImmutable($r['connect_datetime'])
                );
            }
            $r['manifest'] = $manifest;
            $tmp[] = $this->parseRow($r);
        }
        $pagination->setItems($tmp);
        return $pagination;
    }

    public function fetchRoundsForCkeyForChart(
        string $ckey,
        int $range = 180
    ): array {
        $currentRounds = $this->serverInformationService->getCurrentRounds();
        $qb = $this->qb();

        $connSub = $this->qb();
        $connSub
            ->select('c.round_id, MAX(c.datetime) AS connect_datetime, c.ckey')
            ->from('connection_log', 'c')
            ->andWhere('c.ckey = ' . $connSub->createNamedParameter($ckey))
            ->groupBy('c.round_id');

        $qb
            ->select(
                'DATE_FORMAT(cl.connect_datetime, "%Y-%m-%d") as date',
                'count(r.id) as rounds',
                'r.server_port as port',
                'cl.connect_datetime'
            )
            ->from('round', 'r')
            ->innerJoin(
                'r',
                '(' . $connSub->getSQL() . ')',
                'cl',
                'cl.round_id = r.id AND cl.ckey = ' .
                    $qb->createNamedParameter($ckey)
            )
            ->andWhere('r.id IS NOT NULL')
            ->andWhere('cl.connect_datetime BETWEEN CURDATE() - INTERVAL ' .
            $qb->createNamedParameter($range) .
                ' DAY AND CURDATE()')
            ->groupBy(
                'r.server_port',
                'YEAR(cl.connect_datetime)',
                'MONTH(cl.connect_datetime)',
                'DAY(cl.connect_datetime)'
            )
            ->orderBy('r.id', 'DESC');

        if ($currentRounds) {
            $qb->andWhere('r.id NOT IN (' . implode(',', $currentRounds) . ')');
        }
        $results = $qb->executeQuery()->fetchAllAssociative();
        $end = new DateTimeImmutable();
        $start = $end->sub(new DateInterval(sprintf('P%sD', $range)));
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($start, $interval, $end);
        $fullDates = [];
        foreach ($period as $date) {
            $fullDates[$date->format('Y-m-d')] = []; // default 0
        }
        foreach ($results as $r) {
            $server = $this->serverInformationService
                ->getServerFromPort($r['port'])
                ->getIdentifier();

            $fullDates[$r['date']][$server] = $r['rounds'];
        }
        return $fullDates;
    }

    public function wasCkeyInRound(string $ckey, int $round): bool
    {
        $qb = $this->qb();
        $qb
            ->select('c.id')
            ->from('connection_log', 'c')
            ->where('c.ckey = ' . $qb->createNamedParameter($ckey))
            ->andWhere('c.round_id = ' . $qb->createNamedParameter($round))
            ->executeQuery();
        return $qb->fetchOne();
    }

    public function parseRow(array $result): object
    {
        if ($result['server_port']) {
            $result['server'] = $this->serverInformationService->getServerFromPort(
                $result['server_port']
            );
        } else {
            $result['server'] =
                $this->serverInformationService->getEmptyServer();
        }
        return parent::parseRow($result);
    }
}

<?php

namespace App\Repository;

use App\Entity\Round;
use DateTimeImmutable;

class DeathRepository extends TGRepository
{
    public const TABLE = 'death';
    public const ALIAS = 'd';

    public const COLUMNS = [
        'd.id',
        'd.pod as location',
        'd.x_coord as x',
        'd.y_coord as y',
        'd.z_coord as z',
        'd.tod as datetime',
        'd.job',
        'd.special',
        'd.name',
        'd.byondkey as ckey'
    ];

    public function fetchDeathsForHeatmap(string $map, int $z): array
    {
        $qb = $this->qb();
        $qb
            ->select(
                'concat_ws(",", d.x_coord, d.y_coord) as coord',
                'count(d.id) as deaths'
            )
            ->from(static::TABLE, static::ALIAS)
            ->where('d.mapname = ' . $qb->createNamedParameter($map))
            ->andWhere('d.z_coord = ' . $qb->createNamedParameter($z))
            ->andWhere(
                'd.tod >= DATE_SUB(CURRENT_TIMESTAMP(), INTERVAL 1 YEAR)'
            )
            ->groupBy('d.x_coord', 'd.y_coord');
        $qb->executeQuery();
        return $qb->fetchAllKeyValue();
    }

    public function fetchDeathsForRound(Round $round): array
    {
        $qb = $this->qb();
        $qb
            ->select(...static::COLUMNS)
            ->from(static::TABLE, static::ALIAS)
            ->where('d.round_id =' .
                $qb->createNamedParameter($round->getId()));
        $results = $qb->executeQuery()->fetchAllAssociative();
        foreach ($results as &$r) {
            $r['datetime'] = new DateTimeImmutable($r['datetime']);
            if ('' == $r['special']) {
                $r['special'] = null;
            }

            // $r = $this->parseRow($r);
        }
        return $results;
    }
}

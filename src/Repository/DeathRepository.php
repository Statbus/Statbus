<?php

namespace App\Repository;

class DeathRepository extends TGRepository
{
    public const TABLE = 'death';
    public const ALIAS = 'd';

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
}

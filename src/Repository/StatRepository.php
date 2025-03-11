<?php

namespace App\Repository;

use App\Entity\Round;
use App\Entity\Stat;
use App\Repository\TGRepository;

class StatRepository extends TGRepository
{

    public const COLUMNS = [
        'f.id',
        'f.datetime',
        'f.round_id as round',
        'f.key_name as `key`',
        'f.key_type as `type`',
        'f.version',
        'f.json'
    ];

    public const TABLE = 'feedback';
    public const ALIAS = 'f';

    public const ENTITY = Stat::class;

    public function getStatsForRound(Round $round, ?array $stats = null): array
    {

        $query = $this->getBaseQuery();
        $query->where('f.round_id = ' . $query->createNamedParameter($round->getId()));
        if ($stats) {
            $stats = "('" . implode("','", $stats) . "')";
            $query->andWhere("f.key_name in $stats");
        }
        $results = $query->executeQuery()->fetchAllAssociative();
        foreach ($results as $k => &$r) {
            $r = $this->parseRow($r);
        }
        return $results;
    }
}

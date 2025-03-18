<?php

namespace App\Repository;

use App\Service\RankService;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class StatbusRepository
{

    public const ENTITY = null;

    public const TABLE = '';
    public const ALIAS = '';

    public const COLUMNS = [];

    public const ORDERBY = '';

    public const PER_PAGE = 60;

    public function __construct(
        protected Connection $statbusConnection,
        protected RankService $rankService
    ) {}

    public function qb(): QueryBuilder
    {
        return $this->statbusConnection->createQueryBuilder();
    }

    public function getBaseQuery(): QueryBuilder
    {
        $qb = $this->qb();
        $qb->select(...static::COLUMNS)
            ->from(static::TABLE, static::ALIAS);
        if (static::ORDERBY) {
            $qb->orderBy(static::ORDERBY, 'DESC');
        }
        return $qb;
    }

    public function findOneBy(string $key, int|string $value): mixed
    {
        $qb = $this->getBaseQuery();
        $result = $qb->where(
            static::ALIAS . '.' . $key . "=" . $qb->createNamedParameter($value)
        )
            ->executeQuery()
            ->fetchAssociative();
        if (!$result) {
            return null;
        }
        return $this->parseRow($result);
    }

    public function parseRow(array $result): object
    {
        return call_user_func(static::ENTITY . '::new', $result);
    }
}

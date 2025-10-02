<?php

namespace App\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use IPTools\IP;
use IPTools\Network;

class TelemetryRepository extends TGRepository
{
    public function getBaseQuery(): QueryBuilder
    {
        $qb = $this->qb();
        $qb->select(
            'first_round_id',
            'latest_round_id',
            'ckey',
            'address',
            'computer_id',
            'telemetry_ckey'
        )->from('telemetry_connections')->orderBy('ckey', 'DESC');
        return $qb;
    }

    public function findTelemetry(?string $ckey, mixed $ip, ?int $cid): array
    {
        $qb = $this->getBaseQuery();
        if ($ckey) {
            $qb->orWhere($qb->expr()->or(
                $qb->expr()->like('ckey', ':ckey'),
                $qb->expr()->like('telemetry_ckey', ':ckey')
            ))->setParameter('ckey', $ckey);
        }
        if ($cid) {
            $qb->orWhere('computer_id LIKE :cid')->setParameter('cid', $cid);
        }
        if ($ip) {
            if ($ip instanceof Network) {
                $qb->orWhere('address BETWEEN :start AND :end')->setParameter(
                    'start',
                    (new IP($ip->getFirstIP()))->toLong()
                )->setParameter('end', (new IP($ip->getLastIP()))->toLong());
            } else {
                $qb->orWhere('address = :ip')->setParameter(
                    'ip',
                    $ip->toLong()
                );
            }
        }

        $result = $qb->executeQuery();
        return $result->fetchAllAssociative();
    }
}

<?php

namespace App\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use IPTools\IP;
use IPTools\Network;

class ConnectionRepository extends TGRepository
{

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
            )->from('connection_log')
            ->groupBy('day', 'server_ip', 'server_port', 'ckey', 'ip', 'computerid')
            ->orderBy('day', 'DESC');
        return $qb;
    }

    public function findConnections(?string $ckey, mixed $ip, ?int $cid): array
    {
        $qb = $this->getBaseQuery();
        if ($ckey) {
            $qb->orWhere('ckey LIKE :ckey')
                ->setParameter('ckey', '%' . $ckey . '%');
        }
        if ($cid) {
            $qb->orWhere('computerid LIKE :cid')
                ->setParameter('cid', '%' . $cid . '%');
        }
        if ($ip) {
            if ($ip instanceof Network) {
                $qb->orWhere('ip BETWEEN :start AND :end')
                    ->setParameter('start', (new IP($ip->getFirstIP()))->toLong())
                    ->setParameter('end', (new IP($ip->getLastIP()))->toLong());
            } else {
                $qb->orWhere('ip = :ip')
                    ->setParameter('ip', $ip->toLong());
            }
        }
        $result = $qb->executeQuery();
        return $result->fetchAllAssociative();
    }
}

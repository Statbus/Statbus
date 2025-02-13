<?php

namespace App\Repository;

use App\Entity\Round;
use App\Security\User;
use App\Service\ServerInformationService;
use DateTime;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Exception;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class RoundRepository extends ServiceEntityRepository
{
    public function __construct(
        private Connection $connection,
        private PaginatorInterface $paginatorInterface,
        private ServerInformationService $serverInformationService
    ) {}

    private function getBaseQuery(): QueryBuilder
    {
        $rounds = implode(',', $this->serverInformationService->getCurrentRounds());
        $qb = $this->connection->createQueryBuilder();
        $qb->select(
            'r.id',
            'r.initialize_datetime as init',
            'r.start_datetime as start',
            'r.shutdown_datetime as shutdown',
            'r.end_datetime as end',
            'r.server_ip',
            'r.server_port',
            'r.commit_hash',
            'r.game_mode',
            'r.game_mode_result',
            'r.end_state',
            'r.shuttle_name',
            'r.map_name',
            'r.station_name',
            'dt.json as dt'
        )->from('round', 'r')
            ->leftJoin('r', 'feedback', 'dt', 'dt.round_id = r.id AND dt.key_name = "dynamic_threat"')
            ->orderBy('r.start_datetime', 'DESC')
            ->andWhere('r.id NOT IN (' . $rounds . ')');
        return $qb;
    }

    private function parseRow(array $row): Round
    {
        try {
            $threat = json_decode($row['dt'], true)['data'][1];
        } catch (Exception $e) {
            $threat = null;
        }
        return new Round(
            id: $row['id'],
            init: new DateTimeImmutable($row['init']),
            start: $row['start'] ? new DateTimeImmutable($row['start']) : null,
            end: $row['end'] ? new DateTimeImmutable($row['end']) : null,
            shutdown: $row['shutdown'] ? new DateTimeImmutable($row['shutdown']) : null,
            server: $this->serverInformationService->getServerFromPort($row['server_port']),
            commit: $row['commit_hash'],
            result: $row['game_mode_result'],
            state: $row['end_state'],
            shuttle: $row['shuttle_name'],
            map: $row['map_name'],
            name: $row['station_name'],
            threat: $threat
        );
    }

    public function getRounds(int $page = 1): PaginationInterface
    {
        $query = $this->getBaseQuery();
        $pagination = $this->paginatorInterface->paginate($query, $page, 30);
        $tmp = $pagination->getItems();
        foreach ($tmp as &$r) {
            $r = $this->parseRow($r);
        }
        $pagination->setItems($tmp);
        return $pagination;
    }
}

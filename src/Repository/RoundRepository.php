<?php

namespace App\Repository;

use App\Entity\Round;
use App\Service\ServerInformationService;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use Pagerfanta\Doctrine\DBAL\QueryAdapter;
use Pagerfanta\Pagerfanta;

class RoundRepository extends ServiceEntityRepository
{
    public const PER_PAGE = 30;

    protected Pagerfanta $pager;

    public function __construct(
        private Connection $connection,
        private PaginatorInterface $paginatorInterface,
        private ServerInformationService $serverInformationService
    ) {}

    public function getPager(): Pagerfanta
    {
        return $this->pager;
    }

    private function getBaseQuery(): QueryBuilder
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select(
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
            )
            ->from('round', 'r')
            ->leftJoin(
                'r',
                'feedback',
                'dt',
                'dt.round_id = r.id AND dt.key_name = "dynamic_threat"'
            )
            ->orderBy('r.start_datetime', 'DESC');

        $rounds = implode(
            ',',
            $this->serverInformationService->getCurrentRounds()
        );
        if ($rounds) {
            $qb->andWhere('r.id NOT IN (' . $rounds . ')');
        }
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
            shutdown: $row['shutdown']
                ? new DateTimeImmutable($row['shutdown'])
                : null,
            server: $this->serverInformationService->getServerFromPort(
                $row['server_port']
            ),
            commit: $row['commit_hash'],
            result: $row['game_mode_result'],
            state: $row['end_state'],
            shuttle: $row['shuttle_name'],
            map: $row['map_name'],
            name: $row['station_name'],
            threat: $threat
        );
    }

    public function getRounds(int $page = 1): array
    {
        $query = $this->getBaseQuery();
        $countQueryBuilderModifier =
            static function (QueryBuilder $queryBuilder): void {
                $queryBuilder
                    ->select('COUNT(DISTINCT r.id) AS total_results')
                    ->setMaxResults(1);
            };
        $adapter = new QueryAdapter($query, $countQueryBuilderModifier);
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $page,
            static::PER_PAGE
        );
        $this->pager = $pager;
        $data = [];
        foreach ($pager->getCurrentPageResults() as $item) {
            $data[] = $this->parseRow($item);
        }
        return $data;
    }

    public function getRound(int $id): ?Round
    {
        $query = $this->getBaseQuery();
        $query->andWhere('r.id = ' . $query->createNamedParameter($id));
        if (!($result = $query->executeQuery()->fetchAssociative())) {
            return null;
        }
        return $this->parseRow($result);
    }
}

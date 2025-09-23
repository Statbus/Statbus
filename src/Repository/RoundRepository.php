<?php

namespace App\Repository;

use App\Entity\Round;
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
        'r.commit_hash',
        'r.game_mode as mode',
        'r.game_mode_result as result',
        'r.end_state as state',
        'r.shuttle_name',
        'r.map_name as map',
        'r.station_name'
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
        $query = $this->qb();
        $query->select(...static::COLUMNS);
        $query->from('connection_log', 'c');
        $query->leftJoin('c', 'round', 'r', 'r.id = c.round_id');
        $query->andWhere('c.ckey = ' . $query->createNamedParameter($ckey));
        $query->andWhere('r.id IS NOT NULL');
        $query->groupBy('r.id');
        $query->orderBy('r.id', 'DESC');
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

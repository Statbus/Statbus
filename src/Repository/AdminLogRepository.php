<?php

namespace App\Repository;

use App\Entity\AdminLog;
use App\Entity\Player;
use App\Enum\Info\AdminLogOperation;
use App\Service\Player\GetBasicPlayerService;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class AdminLogRepository
{
    public function __construct(
        private Connection $connection,
        private PaginatorInterface $paginatorInterface,
        private GetBasicPlayerService $playerService
    ) {}

    private function getBaseQuery(): QueryBuilder
    {
        $qb = $this->connection->createQueryBuilder();
        $query = $qb
            ->select(
                'l.id',
                'l.datetime',
                'l.round_id as round',
                'l.adminckey',
                'l.operation',
                'l.target',
                'l.log',
                'a.rank as a_rank',
                't.rank as t_rank'
            )
            ->from('admin_log', 'l')
            ->leftJoin('l', 'admin', 'a', 'a.ckey = l.adminckey')
            ->leftJoin('l', 'admin', 't', 't.ckey = l.target')
            ->orderBy('l.datetime', 'DESC');
        return $query;
    }

    public function getAdminLogs(int $page): PaginationInterface
    {
        $query = $this->getBaseQuery();
        $pagination = $this->paginatorInterface->paginate($query, $page, 30);
        $tmp = $pagination->getItems();
        foreach ($tmp as &$i) {
            $i = $this->parseRow($i);
        }
        $pagination->setItems($tmp);
        return $pagination;
    }

    public function getAdminLogsForCkey(Player $player): array
    {
        $query = $this->getBaseQuery();
        $query->where('l.target = ' .
            $query->createNamedParameter($player->getCkey()));
        $result = $query->executeQuery()->fetchAllAssociative();
        foreach ($result as &$r) {
            $r = $this->parseRow($r);
        }
        return $result;
    }

    private function parseRow(array $i): AdminLog
    {
        $action = AdminLogOperation::from($i['operation']);
        if ($action === AdminLogOperation::CHANGE_RANK) {
            $i['t_rank'] = explode(' ', $i['log']);
            $i['t_rank'] = end($i['t_rank']);
        } elseif ($action === AdminLogOperation::REMOVE_ADMIN) {
            $i['t_rank'] = 'Player';
        } elseif ($action === AdminLogOperation::ADD_ADMIN) {
            $i['t_rank'] = 'Player';
        }
        return new AdminLog(
            id: $i['id'],
            datetime: new DateTimeImmutable($i['datetime']),
            round: $i['round'],
            admin: $this->playerService->playerFromCkey(
                $i['adminckey'],
                $i['a_rank']
            ),
            target: $this->playerService->playerFromCkey(
                $i['target'],
                $i['t_rank']
            ),
            action: $action,
            log: $i['log']
        );
    }
}

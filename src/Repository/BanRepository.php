<?php

namespace App\Repository;

use App\Entity\Ban;
use App\Entity\Player;
use App\Entity\Rank;
use App\Security\User;
use App\Service\Player\GetBasicPlayerService;
use App\Service\ServerInformationService;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Exception;
use IPTools\IP;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class BanRepository extends ServiceEntityRepository
{
    public function __construct(
        private Connection $connection,
        private PaginatorInterface $paginatorInterface,
        private GetBasicPlayerService $playerService,
        private ServerInformationService $serverInformationService

    ) {}

    private function parseRow(array $row): Ban
    {
        $target = $row['ckey'] ? $this->playerService->playerFromCkey($row['ckey'], $row['c_rank']) : null;
        return new Ban(
            id: $row['id'],
            bantime: new DateTimeImmutable($row['bantime']),
            ip: $row['ip'] ? IP::parse($row['ip']) : null,
            cid: $row['computerid'] === 'LEGACY' ? 0 : $row['computerid'],
            round: $row['round'],
            roles: $row['role'],
            expiration: $row['expiration'] ? new DateTimeImmutable($row['expiration']) : null,
            unbanned: $row['unbanned_datetime'] ? new DateTimeImmutable($row['unbanned_datetime']) : null,
            reason: $row['reason'],
            target: $target,
            admin: $this->playerService->playerFromCkey($row['a_ckey'], $row['a_rank']),
            unbanner: $row['unbanned_ckey'] ? $this->playerService->playerFromCkey($row['unbanned_ckey'], $row['c_rank']) : null,
            server: $this->serverInformationService->getServerFromPort($row['server_port']),
            banIds: explode(', ', $row['banIds'])
        );
    }

    private function getBaseQuery(): QueryBuilder
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select(
                'b.id',
                'b.bantime',
                'b.server_ip',
                'b.server_port',
                'b.round_id as round',
                'GROUP_CONCAT(b.role SEPARATOR ", ") as `role`',
                'GROUP_CONCAT(b.id SEPARATOR ", ") as `banIds`',
                'b.expiration_time as expiration',
                'b.reason',
                'b.ckey',
                'b.ip',
                'b.computerid',
                'b.a_ckey',
                'b.unbanned_datetime',
                'b.unbanned_ckey',
                'c.rank as c_rank',
                'a.rank as a_rank',
                'u.rank as u_rank',
                'CASE 
                    WHEN b.expiration_time < NOW() THEN 0
                    WHEN b.unbanned_ckey IS NOT NULL THEN 0
                    ELSE 1 
                END as `active`'
            )
            ->from('ban', 'b')
            ->leftJoin('b', 'admin', 'c', 'c.ckey = b.ckey')
            ->leftJoin('b', 'admin', 'a', 'a.ckey = b.a_ckey')
            ->leftJoin('b', 'admin', 'u', 'u.ckey = b.unbanned_ckey')
            ->orderBy('b.bantime', 'DESC')
            ->andWhere('b.round_id IS NOT NULL')
            ->groupBy('b.bantime', 'b.ckey', 'b.server_port');
        return $qb;
    }

    public function getBans(int $page): PaginationInterface
    {
        $query = $this->getBaseQuery();
        $pagination = $this->paginatorInterface->paginate($query, $page, 30, [
            'distinct' => false
        ]);
        $pagination->setTotalItemCount($this->countBans($query));
        $tmp = $pagination->getItems();
        foreach ($tmp as &$i) {
            $i = $this->parseRow($i);
        }
        $pagination->setItems($tmp);
        return $pagination;
    }

    public function getBansForPlayer(int $page, User|string $player, bool $censor = false): PaginationInterface
    {
        if ($player instanceof User) {
            $player = $player->getCkey();
        }
        $query = $this->getBaseQuery();
        $query->where('b.ckey = ' . $query->createNamedParameter($player));
        $pagination = $this->paginatorInterface->paginate($query, $page, 30, [
            'distinct' => false
        ]);
        $pagination->setTotalItemCount($this->countBans($query));
        $tmp = $pagination->getItems();
        foreach ($tmp as &$i) {
            $i = $this->parseRow($i);
            if ($censor) {
                $i->censor();
            }
        }
        $pagination->setItems($tmp);
        return $pagination;
    }

    public function getBansByPlayer(int $page, User|string $admin, bool $censor = false): PaginationInterface
    {
        if ($admin instanceof User) {
            $admin = $admin->getCkey();
        }
        $query = $this->getBaseQuery();
        $query->where('b.a_ckey = ' . $query->createNamedParameter($admin));
        $pagination = $this->paginatorInterface->paginate($query, $page, 30, [
            'distinct' => false
        ]);
        $pagination->setTotalItemCount($this->countBans($query));
        $tmp = $pagination->getItems();
        foreach ($tmp as &$i) {
            $i = $this->parseRow($i);
            if ($censor) {
                $i->censor();
            }
        }
        $pagination->setItems($tmp);
        return $pagination;
    }

    public function getBansForRound(int $page, int $round): PaginationInterface
    {
        $query = $this->getBaseQuery();
        $query->where('b.round_id = ' . $query->createNamedParameter($round));
        $pagination = $this->paginatorInterface->paginate($query, $page, 30, [
            'distinct' => false
        ]);
        $pagination->setTotalItemCount($this->countBans($query));
        $tmp = $pagination->getItems();
        foreach ($tmp as &$i) {
            $i = $this->parseRow($i);
        }
        $pagination->setItems($tmp);
        return $pagination;
    }

    public function getBan(int $ban): Ban
    {
        $query = $this->getBaseQuery();
        $query->addSelect(
            "GROUP_CONCAT(r.id SEPARATOR ', ') as `banIds`",
            'GROUP_CONCAT(r.role SEPARATOR ", ") as `role`'
        );
        $query->innerJoin('b', 'ban', 'r', 'r.bantime = b.bantime AND r.ckey = b.ckey');
        $query->where('b.id = ' . $query->createNamedParameter($ban));
        return $this->parseRow($query->executeQuery()->fetchAssociative());
    }


    public function getPlayerStanding(Player $player)
    {
        $qb = $this->connection->createQueryBuilder();
        $result = $qb->select(
            'b.role',
            'b.id',
            'b.expiration_time',
        )->from('ban', 'b')
            ->where('b.ckey = ' . $qb->createNamedParameter($player->getCkey()))
            ->andWhere($qb->expr()->or(
                $qb->expr()->and(
                    $qb->expr()->gt('b.expiration_time', 'NOW()'),
                    $qb->expr()->isNull('b.unbanned_ckey')
                ),
                $qb->expr()->and(
                    $qb->expr()->isNull('b.expiration_time'),
                    $qb->expr()->isNull('b.unbanned_ckey')
                )
            ))
            ->executeQuery()->fetchAllAssociative();
        return $result;
    }

    private function countBans(QueryBuilder $qb): int
    {
        $qb->select('count(*) as cnt');
        $qb->resetOrderBy();
        return $qb->executeQuery()->rowCount();
    }

    // $sqlwherea = array("ckey is not null");
    // $sqlwherea[] = "expiration_time is null"; //only permabanns
    // $sqlwherea[] = "role = 'Server'"; //only server bans
    // $sqlwherea[] = "(unbanned_datetime is null OR DATEDIFF(unbanned_datetime, bantime) > 7)"; //stop showing bans that were unbanned within a week, (centcomdb will treat this as a ban delete and remove it from their copy)
    // $sqlwherea[] = "DATE_ADD(bantime, INTERVAL 60 MINUTE) < NOW()"; //wait an hour before showing a ban publically.
    // $sqlwherea[] = "bantime >= CAST('2021-04-23 21:35:00' AS datetime)"; //only bans added after a certain date

    public function getPublicBans(int $page = 1, bool $censor = true): PaginationInterface
    {
        $query = $this->getBaseQuery();
        $query
            ->andWhere('b.ckey IS NOT NULL')
            ->andWhere('b.expiration_time IS NULL')
            ->andWhere("b.role = 'Server'")
            ->andWhere('(b.unbanned_datetime IS NULL OR DATEDIFF(b.unbanned_datetime, bantime) > 7)')
            ->andWhere('DATE_ADD(bantime, INTERVAL 60 MINUTE) < NOW()')
            ->andWhere("bantime >= '2021-04-23 21:35:00'");

        $pagination = $this->paginatorInterface->paginate($query, $page, 30, [
            'distinct' => false
        ]);
        $pagination->setTotalItemCount($this->countBans($query));
        $tmp = $pagination->getItems();
        foreach ($tmp as &$i) {
            $i = $this->parseRow($i);
            if ($censor) {
                $i->censor();
            }
        }
        $pagination->setItems($tmp);
        return $pagination;
    }
}

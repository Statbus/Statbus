<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\Player;
use App\Entity\Search;
use App\Security\User;
use Doctrine\DBAL\Query\QueryBuilder;
use IPTools\Network;
use Knp\Component\Pager\Pagination\PaginationInterface;

class MessageRepository extends TGRepository
{

    public const TABLE = 'messages';
    public const ALIAS = 'm';

    public const ENTITY = Message::class;

    public const ORDERBY = 'm.timestamp';

    public const COLUMNS = [
        'm.id',
        'm.type',
        'm.targetckey',
        'm.adminckey',
        'm.text',
        'm.timestamp',
        'm.server_ip',
        'm.server_port',
        'm.round_id as round',
        'm.secret',
        'm.expire_timestamp as expiration',
        'm.severity',
        'm.playtime',
        'm.lasteditor',
        'm.edits',
        'm.deleted',
        'm.deleted_ckey',
        't.rank as t_rank',
        'a.rank as a_rank'
    ];

    public function getBaseQuery(): QueryBuilder
    {
        $qb = parent::getBaseQuery();
        $qb->where('m.deleted != 1');
        $qb->leftJoin(static::ALIAS, 'admin', 't', 't.ckey = m.targetckey');
        $qb->leftJoin(static::ALIAS, 'admin', 'a', 'a.ckey = m.adminckey');
        return $qb;
    }

    public function getMessages(int $page, Search $search): PaginationInterface
    {
        $query = $this->getBaseQuery();
        if ($search->isActive()) {
            $query->resetWhere();
            if ($search->getCkey()) {
                $query->orWhere('m.targetckey LIKE :ckey')
                    ->setParameter('ckey', '%' . $search->getCkey() . '%');
            }
            if ($search->getACkey()) {
                $query->orWhere('m.adminckey LIKE :ckey')
                    ->setParameter('ckey', '%' . $search->getACkey() . '%');
            }
            if ($search->getText()) {
                $query->orWhere('m.text LIKE :text')
                    ->setParameter('text', '%' . $search->getText() . '%');
            }
        }
        $query->andWhere('m.deleted != 1');
        $pagination = $this->paginatorInterface->paginate($query, $page, 30);
        $tmp = $pagination->getItems();
        foreach ($tmp as &$r) {
            $r['targetRank'] = $this->rankService->getRankByName($r['t_rank']);
            $r['adminRank'] = $this->rankService->getRankByName($r['a_rank']);
            $r['server'] = $this->serverInformationService->getServerFromPort($r['server_port']);
            $r = $this->parseRow($r);
        }
        $pagination->setItems($tmp);
        return $pagination;
    }

    public function getMessagesForPlayer(
        int $page,
        User|string $player,
        bool $skipSecret = false
    ): PaginationInterface {
        if ($player instanceof User) {
            $player = $player->getCkey();
        }
        $query = $this->getBaseQuery();
        $query->where('m.deleted != 1');
        $query->andWhere('m.targetckey = ' . $query->createNamedParameter($player));
        if ($skipSecret) {
            $query->andWhere('m.secret != 1');
        }
        $pagination = $this->paginatorInterface->paginate($query, $page, 30);
        $tmp = $pagination->getItems();
        foreach ($tmp as &$r) {
            $r['targetRank'] = $this->rankService->getRankByName($r['t_rank']);
            $r['adminRank'] = $this->rankService->getRankByName($r['a_rank']);
            $r['server'] = $this->serverInformationService->getServerFromPort($r['server_port']);
            $r = $this->parseRow($r);
        }
        $pagination->setItems($tmp);
        return $pagination;
    }

    public function getMessagesForRound(
        int $page,
        int $round
    ): PaginationInterface {
        $query = $this->getBaseQuery();
        $query->where('m.deleted != 1');
        $query->andWhere('m.round_id = ' . $query->createNamedParameter($round));
        $pagination = $this->paginatorInterface->paginate($query, $page, 30);
        $tmp = $pagination->getItems();
        foreach ($tmp as &$r) {
            $r['targetRank'] = $this->rankService->getRankByName($r['t_rank']);
            $r['adminRank'] = $this->rankService->getRankByName($r['a_rank']);
            $r['server'] = $this->serverInformationService->getServerFromPort($r['server_port']);
            $r = $this->parseRow($r);
        }
        $pagination->setItems($tmp);
        return $pagination;
    }

    public function getMessage(int $id): ?Message
    {
        $query = $this->getBaseQuery();
        $query->where('m.deleted != 1');
        $query->andWhere('m.id = ' . $query->createNamedParameter($id));
        $result = $query->executeQuery()->fetchAssociative();
        $result['targetRank'] = $this->rankService->getRankByName(
            $result['t_rank']
        );
        $result['adminRank'] = $this->rankService->getRankByName(
            $result['a_rank']
        );
        $result['server'] = $this->serverInformationService->getServerFromPort(
            $result['server_port']
        );
        $result = $this->parseRow($result);
        return $result;
    }
}

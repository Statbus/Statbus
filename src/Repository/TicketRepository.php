<?php

namespace App\Repository;

use App\Entity\Player;
use App\Entity\Ticket;
use App\Enum\Ticket\Action;
use DateTimeImmutable;
use Doctrine\DBAL\Query\QueryBuilder;
use Exception;
use Knp\Component\Pager\Pagination\PaginationInterface;

class TicketRepository extends TGRepository
{
    public const TABLE = 'ticket';
    public const ALIAS = 't';

    public const ENTITY = Ticket::class;

    public const ORDERBY = 't.timestamp';

    public const COLUMNS = [
        't.id',
        't.server_ip as serverIp',
        't.server_port as port',
        't.round_id as `round`',
        't.ticket',
        't.action',
        't.message',
        't.timestamp',
        't.recipient as r_ckey',
        't.sender as s_ckey',
        't.urgent',
    ];

    public function getBaseQuery(): QueryBuilder
    {
        $replyCountQuery    = $this->replyCountSubquery();
        $senderRankQuery    = $this->senderRankSubquery();
        $recipientRankQuery = $this->recipientRankSubquery();

        $qb = parent::getBaseQuery();
        $qb->addSelect(
            "($senderRankQuery) as s_rank",
            "($recipientRankQuery) as r_rank",
            "($replyCountQuery) as replies"
        );
        $qb->orderBy('t.round_id', 'DESC');
        return $qb;
    }

    private function replyCountSubquery(): string
    {
        return $this->qb()
            ->select("COUNT(*)")
            ->from('ticket')
            ->where('round_id = t.round_id')
            ->andWhere('ticket = t.ticket')
            ->getSQL();
    }

    private function senderRankSubquery(): string
    {
        return $this->qb()
            ->select('rank')
            ->from('admin')
            ->where('ckey = s_ckey')
            ->getSQL();
    }

    private function recipientRankSubquery(): string
    {
        return $this->qb()
            ->select('rank')
            ->from('admin')
            ->where('ckey = r_ckey')
            ->getSQL();
    }

    public function parseRow(array $r): object
    {
        $action = Action::from($r['action']);
        // Disgusting hack alert:
        // Connection (Reconnect and Disconnect) action list the recipient as
        // the target for the message, even though there is no target. For that
        // reason, we have to swap the recipient into the sender on these
        // actions.
        //We also do the same thing with reopened tickets
        if (($action->isConnectAction() || $action === Action::REOPENED) && ! $r['s_ckey']) {
            $r['s_ckey'] = $r['r_ckey'];
            $r['r_ckey'] = null;
        }
        $r['server'] = $this->serverInformationService->getServerFromPort(
            $r['port']
        );
        $r['sender'] = new Player(
            ckey: $r['s_ckey'],
            firstSeen: new DateTimeImmutable(),
            lastSeen: new DateTimeImmutable(),
            rank: $this->rankService->getRankByName($r['s_rank'])
        );
        $r['recipient'] = null;
        if ($r['r_ckey']) {
            $r['recipient'] = new Player(
                ckey: $r['r_ckey'],
                firstSeen: new DateTimeImmutable(),
                lastSeen: new DateTimeImmutable(),
                rank: $this->rankService->getRankByName($r['r_rank'])
            );
        }
        $r['message'] = $this->HTMLSanitizerService->sanitizeString(
            $r['message']
        );
        $r = parent::parseRow($r);
        return $r;
    }

    public function getTickets(int $page): PaginationInterface
    {
        $query = $this->getBaseQuery();
        $query->where('t.round_id != 0')
            ->andWhere('t.action = "Ticket Opened"');
        $pagination = $this->paginatorInterface->paginate($query, $page, 30);
        $tmp        = $pagination->getItems();
        foreach ($tmp as &$r) {
            $r = $this->parseRow($r);
        }
        $pagination->setItems($tmp);
        return $pagination;
    }
    public function getTicketsBy(
        string $key,
        string $value,
        int $page
    ): PaginationInterface {
        $query = $this->getBaseQuery();
        $query->where('t.round_id != 0')
            ->andWhere('t.action = "Ticket Opened"');
        $query->andWhere($key . ' = ' . $query->createNamedParameter($value));
        $query->resetOrderBy();
        $query->addOrderBy('t.ticket', 'ASC');
        $pagination = $this->paginatorInterface->paginate($query, $page, 30);
        $tmp        = $pagination->getItems();
        foreach ($tmp as &$r) {
            $r = $this->parseRow($r);
        }
        $pagination->setItems($tmp);
        return $pagination;
    }

    public function getTicket(int $round, int $ticket): array
    {
        if ($round === 0) {
            throw new Exception("Round ID invalid");
        }
        $query = $this->getBaseQuery();
        $query->andWhere('t.ticket = ' . $query->createNamedParameter($ticket))
            ->andWhere('t.round_id =' . $query->createNamedParameter($round));
        $query->addOrderBy('t.timestamp', 'ASC');
        $results = $query->executeQuery()->fetchAllAssociative();
        foreach ($results as &$r) {
            $r = $this->parseRow($r);
        }
        return $results;
    }

    public function getTicketsByCkey(
        string $ckey,
        int $page
    ): PaginationInterface {
        $replyCountQuery    = $this->replyCountSubquery();
        $senderRankQuery    = $this->senderRankSubquery();
        $recipientRankQuery = $this->recipientRankSubquery();

        $ckeyExistsQuery = $this->qb()
            ->select('1')
            ->from('ticket')
            ->where('round_id = tt.round_id')
            ->andWhere('ticket = tt.ticket')
            ->andWhere('(sender = :ckey OR recipient = :ckey)')
            ->getSQL();

        $minTicketIdQuery = $this->qb()
            ->select('MIN(id) AS id')
            ->from('ticket', 'tt')
            ->where('round_id > 0')
            ->andWhere("EXISTS ($ckeyExistsQuery)")
            ->groupBy('round_id, ticket')
            ->getSQL();

        $query = $this->qb()
            ->select(
                't.id',
                't.server_ip AS serverIp',
                't.server_port AS port',
                't.round_id AS round',
                't.ticket',
                't.action',
                't.message',
                't.timestamp',
                't.recipient AS r_ckey',
                't.sender AS s_ckey',
                "($senderRankQuery) AS s_rank",
                "($recipientRankQuery) AS r_rank",
                "($replyCountQuery) AS replies",
                't.urgent'
            )
            ->from('ticket', 't')
            ->innerJoin('t', "($minTicketIdQuery)", 'f', 't.id = f.id')
            ->orderBy('t.id', 'DESC')
            ->setParameter('ckey', $ckey);

        $pagination = $this->paginatorInterface->paginate(
            $query,
            $page,
            30,
        );

        $tmp = $pagination->getItems();
        foreach ($tmp as &$r) {
            $r = $this->parseRow($r);
        }
        $pagination->setItems($tmp);
        return $pagination;
    }
}

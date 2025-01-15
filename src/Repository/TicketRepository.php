<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\Player;
use App\Entity\Ticket;
use App\Enum\Ticket\Action;
use App\Security\User;
use DateTimeImmutable;
use Doctrine\DBAL\Query\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\Paginator;

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
        'r.rank as r_rank',
        's.rank as s_rank',
        'c.replies as `replies`',
        't.urgent',
    ];

    public function getBaseQuery(): QueryBuilder
    {
        $qb = parent::getBaseQuery();
        $qb->leftJoin(static::ALIAS, 'admin', 'r', 'r.ckey = t.recipient');
        $qb->leftJoin(static::ALIAS, 'admin', 's', 's.ckey = t.sender');
        $qb->leftJoin(static::ALIAS, '(' . $this->replyCountSubquery() . ')', 'c', 'c.round_id = t.round_id and c.ticket = t.ticket');
        $qb->orderBy('t.round_id', 'DESC');
        return $qb;
    }

    private function replyCountSubquery(): string
    {
        $qb = $this->qb();
        $qb->select(
            'round_id',
            'ticket',
            'COUNT(id) as `replies`',
        )
            ->from('ticket')
            ->groupBy('round_id', 'ticket');
        return $qb->getSQL();
    }

    public function parseRow(array $r): object
    {
        $action = Action::from($r['action']);
        // Disgusting hack alert: 
        // Connection (Reconnect and Disconnect) action list the recipient as
        // the target for the message, even though there is no target. For that
        // reason, we have to swap the recipient into the sender on these 
        // actions.
        if ($action->isConnectAction() && !$r['s_ckey']) {
            $r['s_ckey'] = $r['r_ckey'];
            $r['r_ckey'] = null;
        }
        $r['server'] = $this->serverInformationService->getServerFromPort($r['port']);
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
        $r['message'] = $this->HTMLSanitizerService->sanitizeString($r['message']);
        $r = parent::parseRow($r);
        return $r;
    }

    public function getTickets(int $page): PaginationInterface
    {
        $query = $this->getBaseQuery();
        $query->where('t.round_id != 0')
            ->andWhere('t.action = "Ticket Opened"');
        $pagination = $this->paginatorInterface->paginate($query, $page, 30);
        $tmp = $pagination->getItems();
        foreach ($tmp as &$r) {
            $r = $this->parseRow($r);
        }
        $pagination->setItems($tmp);
        return $pagination;
    }
    public function getTicketsBy(string $key, string $value, int $page): PaginationInterface
    {
        $query = $this->getBaseQuery();
        $query->where('t.round_id != 0')
            ->andWhere('t.action = "Ticket Opened"');
        $query->andWhere($key . ' = ' . $query->createNamedParameter($value));
        $query->addOrderBy('t.ticket', 'DESC');
        $pagination = $this->paginatorInterface->paginate($query, $page, 30);
        $tmp = $pagination->getItems();
        foreach ($tmp as &$r) {
            $r = $this->parseRow($r);
        }
        $pagination->setItems($tmp);
        return $pagination;
    }

    public function getTicket(int $round, int $ticket): array
    {
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
        $subQuery = $this->qb();
        $subQuery
            ->select('round_id', 'ticket', 'COUNT(id) as replies', 'MAX(id) as last_id')
            ->from('ticket')
            ->groupBy('round_id', 'ticket');

        $query = $this->qb();
        $query
            ->select(
                'first_tickets.id',
                'first_tickets.server_ip AS serverIp',
                'first_tickets.server_port AS port',
                'first_tickets.round_id AS round',
                'first_tickets.ticket',
                'first_tickets.action',
                'first_tickets.message',
                'first_tickets.timestamp',
                'first_tickets.recipient AS r_ckey',
                'first_tickets.sender AS s_ckey',
                'r.rank AS r_rank',
                's.rank AS s_rank',
                '(SELECT action FROM ticket WHERE id = c.last_id LIMIT 1) AS status',
                'c.replies',
                'first_tickets.urgent'
            )
            ->from('ticket')
            ->leftJoin(
                'ticket',
                '(' . $subQuery->getSQL() . ')',
                'c',
                'c.round_id = ticket.round_id AND c.ticket = ticket.ticket'
            )
            ->innerJoin(
                'ticket',
                'ticket',
                'first_tickets',
                'first_tickets.round_id = ticket.round_id AND first_tickets.ticket = ticket.ticket AND first_tickets.action = \'Ticket Opened\' AND ticket.round_id != 0'
            )
            ->leftJoin('ticket', 'admin', 'r', 'r.ckey = first_tickets.recipient')
            ->leftJoin('ticket', 'admin', 's', 's.ckey = first_tickets.sender')
            ->where(
                $query->expr()->or(
                    $query->expr()->eq(
                        'ticket.recipient',
                        $query->createNamedParameter($ckey)
                    ),
                    $query->expr()->eq(
                        'ticket.sender',
                        $query->createNamedParameter($ckey)
                    )
                )
            )
            ->groupBy('ticket.round_id', 'ticket.ticket')
            ->orderBy('id', 'DESC');

        $countQuery = $this->qb();
        $countQuery
            ->select('COUNT(DISTINCT ticket.round_id, ticket.ticket) AS total')
            ->from('ticket')
            ->leftJoin('ticket', 'admin', 'r', 'r.ckey = ticket.recipient')
            ->leftJoin('ticket', 'admin', 's', 's.ckey = ticket.sender')
            ->leftJoin(
                'ticket',
                '(' . $subQuery->getSQL() . ')',
                'c',
                'c.round_id = ticket.round_id AND c.ticket = ticket.ticket'
            )
            ->innerJoin(
                'ticket',
                'ticket',
                'first_tickets',
                'first_tickets.round_id = ticket.round_id AND first_tickets.ticket = ticket.ticket AND first_tickets.action = \'Ticket Opened\' AND ticket.round_id != 0'
            )
            ->where(
                $countQuery->expr()->or(
                    $countQuery->expr()->eq(
                        'ticket.recipient',
                        $countQuery->createNamedParameter($ckey)
                    ),
                    $countQuery->expr()->eq(
                        'ticket.sender',
                        $countQuery->createNamedParameter($ckey)
                    )
                )
            );
        //UNFORTUNATELY I am really dumb so for the time being we have an extra
        //query in the pagination until I figure out how to tell it not to do
        //that.
        $pagination = $this->paginatorInterface->paginate(
            $query,
            $page,
            30,
        );
        $pagination->setTotalItemCount(
            $countQuery->executeQuery()->fetchFirstColumn()[0]
        );

        $tmp = $pagination->getItems();
        foreach ($tmp as &$r) {
            $r = $this->parseRow($r);
        }
        $pagination->setItems($tmp);
        return $pagination;
    }
}

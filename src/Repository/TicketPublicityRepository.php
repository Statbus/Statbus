<?php

namespace App\Repository;

class TicketPublicityRepository extends StatbusRepository
{
    // public function findTicketStatus(int $round, int $ticket): array
    // {
    //     $qb = $this->qb();
    //     $qb->select('');
    // }

    public function getTicketIdentifier(int $round, int $ticket): ?string
    {
        $qb = $this->qb();
        $result = $qb
            ->select('identifier')
            ->from('public_ticket')
            ->where('round = ' . $qb->createNamedParameter($round))
            ->andWhere('ticket = ' . $qb->createNamedParameter($ticket))
            ->executeQuery()
            ->fetchOne();
        if (!$result) {
            return null;
        }
        return $result;
    }

    public function makeTicketPublic(
        int $round,
        int $ticket,
        string $identifier
    ): void {
        $qb = $this->qb();
        $qb
            ->insert('public_ticket')
            ->values([
                'round' => $qb->createNamedParameter($round),
                'ticket' => $qb->createNamedParameter($ticket),
                'identifier' => $qb->createNamedParameter($identifier)
            ])
            ->executeStatement();
    }

    public function makeTicketPrivate(string $identifier)
    {
        $qb = $this->qb();
        $qb
            ->delete('public_ticket')
            ->where('identifier = ' . $qb->createNamedParameter($identifier))
            ->executeStatement();
    }

    public function getTicketByIdentifier(string $identifier): ?array
    {
        $qb = $this->qb();
        $result = $qb
            ->select('round', 'ticket')
            ->from('public_ticket')
            ->where('identifier = ' . $qb->createNamedParameter($identifier))
            ->executeQuery()
            ->fetchAssociative();
        if (!$result) {
            return null;
        }
        return $result;
    }
}

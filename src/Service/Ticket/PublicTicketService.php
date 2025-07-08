<?php

namespace App\Service\Ticket;

use App\Repository\TicketPublicityRepository;
use App\Security\User;
use Exception;

class PublicTicketService
{
    public function __construct(
        private TicketPublicityRepository $ticketPublicityRepository
    ) {}

    public static function canBePublic(array $ticket, User $user): bool
    {
        if (
            $ticket[0]->isAhelp() &&
                $user->getCkey() === $ticket[0]->getSender()->getCkey()
        ) {
            return true;
        }
        if (
            $ticket[0]->isBwoink() &&
                $user->getCkey() === $ticket[0]->getRecipient()->getCkey()
        ) {
            return true;
        }

        return false;
    }

    private function makeTicketPublic(array $ticket, User $user): string
    {
        if (!self::canBePublic($ticket, $user)) {
            throw new Exception('Not allowed');
        }
        $identifier = bin2hex(random_bytes(16));
        $this->ticketPublicityRepository->makeTicketPublic(
            $ticket[0]->getRound(),
            $ticket[0]->getNumber(),
            $identifier
        );
        return $identifier;
    }

    private function makeTicketPrivate(array $ticket, User $user): void
    {
        if (!self::canBePublic($ticket, $user)) {
            throw new Exception('Not allowed');
        }
        $this->ticketPublicityRepository->makeTicketPrivate($ticket[0]->getIdentifier());
    }

    public function toggleTicket(array $ticket, User $user): void
    {
        if (!$ticket[0]->isPublic()) {
            $this->makeTicketPublic($ticket, $user);
        } else {
            $this->makeTicketPrivate($ticket, $user);
        }
    }

    public function getTicketIdentifier(array $ticket, User $user): ?string
    {
        return $this->ticketPublicityRepository->getTicketIdentifier(
            $ticket[0]->getRound(),
            $ticket[0]->getNumber()
        );
    }

    public function getTicketFromIdentifier(string $identifer): ?array
    {
        return $this->ticketPublicityRepository->getTicketByIdentifier(
            $identifer
        );
    }
}

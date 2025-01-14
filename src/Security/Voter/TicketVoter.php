<?php

namespace App\Security\Voter;

use App\Entity\Ticket;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class TicketVoter extends Voter
{
    public const VIEW = 'TICKET_VIEW';

    public function __construct(
        private Security $security
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW]) && $this->subjectIsTickets($subject);
    }

    private function subjectIsTickets(mixed $subject): bool
    {
        if (!is_array($subject)) {
            return false;
        }

        foreach ($subject as $s) {
            if (!$s instanceof Ticket) {
                return false;
            }
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($this->security->isGranted('ROLE_BAN')) {
            return true;
        }
        $participants = [];
        foreach ($subject as $t) {
            $participants[] = $t->getSender();
            $participants[] = $t->getRecipient();
        }
        $participants = array_filter(array_unique($participants));
        switch ($attribute) {
            case self::VIEW:
                foreach ($participants as $p) {
                    if ($p->getCkey() === $user->getCkey()) {
                        return true;
                    }
                }
                break;
        }

        return false;
    }
}

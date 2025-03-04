<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class MessageVoter extends Voter
{
    public const VIEW = 'MESSAGE_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::VIEW])
            && $subject instanceof \App\Entity\Message;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        //Allow access for admins
        if($user->hasRole('ROLE_BAN')){
            return true;
        }

        //Deny access if the note is secret
        if ($subject->isSecret()) {
            return false;
        }
        // Condition check
        switch ($attribute) {
            case self::VIEW:
                return $subject->getTarget()->getCkey() === $user->getCkey();
                break;
        }

        return false;
    }
}

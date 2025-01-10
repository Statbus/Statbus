<?php

namespace App\Domain\User\Service;

use App\Domain\Discord\Repository\DiscordRepository;
use App\Domain\User\Repository\UserRepository;
use App\Domain\User\Data\User;
use Symfony\Component\HttpFoundation\Session\Session;

class AuthenticateUser
{
    public function __construct(
        private DiscordRepository $discordRepository,
        private UserRepository $userRepository,
        private Session $session
    ) {

    }

    public function refreshUser(): ?User
    {
        $ckey = $this->session->get('ckey', false);
        if(!$ckey) {
            return null;
        }
        $user = $this->userRepository->getUserByCkey($ckey);
        $user->setSource($this->session->get('authSource'));
        return $user;
    }

    public function authenticateUserFromDiscord(int $id): User
    {
        $ckey = $this->discordRepository->getCkeyFromLinkedDiscordAccount($id);
        $user = $this->userRepository->getUserByCkey($ckey);

        $user->setSource('discord');
        $this->session->set('authSource', 'discord');

        $this->session->set('ckey', $user->getCkey());

        return $user;
    }

    public function authenticateUserFromForum(string $ckey): User
    {
        $user = $this->userRepository->getUserByCkey($ckey);

        $user->setSource('TG Forum');
        $this->session->set('authSource', 'TG Forum');

        $this->session->set('ckey', $user->getCkey());

        return $user;
    }

    public function authenticateUserFromIp(string $ip): ?User
    {
        $user = $this->userRepository->getUserByLastIp($ip);
        if(!$user) {
            return null;
        }

        $user->setSource('ip');
        $this->session->set('authSource', 'ip');

        $this->session->set('ckey', $user->getCkey());

        return $user;
    }

}

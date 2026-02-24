<?php

namespace App\Controller;

use App\Attribute\FeatureEnabled;
use App\Repository\UserRepository;
use App\Security\TgStationAuthenticator;
use Exception;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

#[FeatureEnabled('auth.tgstation')]
class TgStationController extends AbstractController
{
    public function __construct(
        private UserAuthenticatorInterface $userAuthenticator,
        private UserRepository $userRepository,
        private TgStationAuthenticator $tgStationAuthenticator
    ) {}

    #[Route('/auth/tgforum', name: 'auth.tgstation.start')]
    public function connectAction(ClientRegistry $clientRegistry)
    {
        return $clientRegistry->getClient('tgstation')->redirect(
            ['user', 'user.linked_accounts'],
            []
        );
    }

    #[Route('/auth/tgforum/success', name: 'auth.tgstation.finish')]
    public function connectCheckAction(): void
    {
        throw new Exception('Impossible route!');
    }

    #[Route('/auth/tg', name: 'auth.tg.start')]
    public function connectTGAction(ClientRegistry $clientRegistry)
    {
        return $clientRegistry->getClient('tgstation-2')->redirect([], []);
    }

    #[Route('/auth/tg/success', name: 'auth.tg.finish')]
    public function connectCheckAction2(): void
    {
        throw new Exception('Impossible route!');
    }
}

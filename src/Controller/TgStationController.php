<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Security\TgStationAuthenticator;
use Exception;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

#[Route('/auth/tgforum')]
class TgStationController extends AbstractController
{
    public function __construct(
        private UserAuthenticatorInterface $userAuthenticator,
        private UserRepository $userRepository,
        private TgStationAuthenticator $tgStationAuthenticator
    ) {}

    #[Route('', name: 'auth.tgstation.start')]
    public function connectAction(ClientRegistry $clientRegistry)
    {
        return $clientRegistry->getClient('tgstation')->redirect(
            ['user', 'user.linked_accounts'],
            []
        );
    }

    #[Route('/success', name: 'auth.tgstation.finish')]
    public function connectCheckAction(): void
    {
        throw new Exception('Impossible route!');
    }
}

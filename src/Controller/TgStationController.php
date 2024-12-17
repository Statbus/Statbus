<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Security\User;
use App\Security\TgStationAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

#[Route('/auth/tgforum')]
class TgStationController extends AbstractController
{

    public function __construct(
        private UserAuthenticatorInterface $userAuthenticator,
        private UserRepository $userRepository,
        private TgStationAuthenticator $tgStationAuthenticator
    ) {
    }

    #[Route('', name: 'auth.tgstation.start')]
    public function connectAction(ClientRegistry $clientRegistry)
    {
        return $clientRegistry
            ->getClient('tgstation')
            ->redirect(['user', 'user.linked_accounts'], []);
    }

    #[Route('/success', name: 'auth.tgstation.finish')]
    public function connectCheckAction(Request $request, ClientRegistry $clientRegistry)
    {
        $client = $clientRegistry->getClient('tgstation');
        $accessToken = $client->getAccessToken();
        $oAuthUser = $client->fetchUserFromToken($accessToken);
        $ckey = $oAuthUser->getId();
        $user = $this->userRepository
            ->findByCkey($ckey);
        $this->userAuthenticator->authenticateUser(
            $user,
            $this->tgStationAuthenticator,
            $request
        );
        return $this->redirectToRoute('app.home');
    }
}

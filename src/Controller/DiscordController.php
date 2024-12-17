<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/auth/discord')]
class DiscordController extends AbstractController
{
    #[Route('', name: 'auth.discord.start')]
    public function connectAction(ClientRegistry $clientRegistry)
    {
        return $clientRegistry
            ->getClient('discord')
            ->redirect(['identify'], []);
    }
    #[Route('/check', name: 'auth.discord.finish')]
    public function connectCheckAction(Request $request, ClientRegistry $clientRegistry)
    {
        $client = $clientRegistry->getClient('discord');
        try {
            $user = $client->fetchUser();
            var_dump($user);
            die;
        } catch (IdentityProviderException $e) {
            var_dump($e->getMessage());
            die;
        }
    }
}

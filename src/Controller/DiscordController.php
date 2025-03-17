<?php

namespace App\Controller;

use App\Repository\DiscordVerificationsRepository;
use Exception;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
    public function check()
    {
        throw new Exception("Impossible route!");
    }
}

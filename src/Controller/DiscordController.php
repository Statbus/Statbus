<?php

namespace App\Controller;

use App\Attribute\FeatureEnabled;
use Exception;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

#[FeatureEnabled('auth.discord')]
#[Route('/auth/discord')]
class DiscordController extends AbstractController
{
    #[Route('', name: 'auth.discord.start')]
    public function connectAction(ClientRegistry $clientRegistry)
    {
        return $clientRegistry->getClient('discord')->redirect(
            ['identify'],
            []
        );
    }

    #[Route('/check', name: 'auth.discord.finish')]
    public function check()
    {
        throw new Exception('Impossible route!');
    }
}

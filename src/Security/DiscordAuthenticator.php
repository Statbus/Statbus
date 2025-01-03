<?php


namespace App\Security;

use App\Repository\DiscordVerificationsRepository;
use App\Repository\UserRepository;
use App\Security\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class DiscordAuthenticator extends OAuth2Authenticator implements AuthenticationEntryPointInterface
{

    public function __construct(
        private ClientRegistry $clientRegistry,
        private UserRepository $userRepository,
        private RouterInterface $router,
        private DiscordVerificationsRepository $discordRepository,
    ) {}

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'auth.discord.finish';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('discord');
        $accessToken = $this->fetchAccessToken($client);
        $badge =  new UserBadge($accessToken->getToken(), function () use ($accessToken, $client) {
            $discordUser = $client->fetchUserFromToken($accessToken);
            $ckey = $this->discordRepository->getCkeyFromDiscordId($discordUser->getId());
            $user = $this->userRepository->findByCkey($ckey);
            return $user;
        });
        $passport = new SelfValidatingPassport($badge);
        return $passport;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->router->generate('app.home'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new RedirectResponse(
            '/auth/discord',
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }
}

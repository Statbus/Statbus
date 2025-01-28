<?php


namespace App\Security;

use App\Security\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class TgStationAuthenticator extends OAuth2Authenticator implements AuthenticationEntryPointInterface
{

    public function __construct(
        private ClientRegistry $clientRegistry,
        private EntityManagerInterface $entityManager,
        private RouterInterface $router,
        private bool $allowNonAdmins = true

    ) {}

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'auth.tgstation.success';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('tgstation');
        $accessToken = $this->fetchAccessToken($client);
        $badge =
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client) {
                $tgStationUser = $client->fetchUserFromToken($accessToken);
                $ckey = $tgStationUser->getId();
                $user = $this->entityManager
                    ->getRepository(User::class)
                    ->findOneBy(['ckey' => $ckey]);
                return $user;
            });
        if (!$this->allowNonAdmins && !$badge->getUser()->hasRole('ROLE_BAN')) {
            throw new Exception("Statbus is currently not available to players.");
        }
        $passport = new SelfValidatingPassport($badge);
        return $passport;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new RedirectResponse(
            '/auth/tgforum',
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }
}

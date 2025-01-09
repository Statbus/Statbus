<?php

namespace App\Middleware;

use App\Domain\User\Repository\UserRepository;
use App\Domain\User\Service\AuthenticateUser;
use Cake\Database\Connection;
use ParagonIE\EasyDB\EasyDB;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Views\Twig;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Loads information about an associated user from the session,
 * pulls their information from the database and stores it in the request's attributes.
 */
class UserMiddleware implements MiddlewareInterface
{
    private UserRepository $userRepository;

    private Session $session;
    
    private Twig $twig;

    public function __construct(
        private ContainerInterface $containerInterface,
        private AuthenticateUser $auth
    ) {
        $this->userRepository = new UserRepository($containerInterface->get(Connection::class), $containerInterface->get(EasyDB::class));
        $this->session = $containerInterface->get(Session::class);
        $this->twig = $containerInterface->get(Twig::class);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $ckey = $this->session->get('ckey');
        $user = null;

        if($ckey) {
            $user = $this->userRepository->getUserByCkey($ckey);
            $user->setSource($this->session->get('authSource'));
        } else {
            // If user not stored, attempt to retrieve user based on IP address, if enabled, and authenticate automatically
            if($this->containerInterface->get('settings')['ip_auth']) {
                $user = $this->auth->authenticateUserFromIp($request->getAttribute('ip_address'));
            }
        }

        $this->twig->getEnvironment()->addGlobal('user', $user);

        return $handler->handle($request->withAttribute('user', $user));
    }
}

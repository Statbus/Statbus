<?php

namespace App\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim\Views\Twig;
use App\Domain\User\Data\User;
use App\Exception\StatbusUnauthorizedException;
use Slim\Routing\RouteContext;
use Symfony\Component\HttpFoundation\Session\Session;

abstract class Controller
{
    private $response;
    private $request;
    private ?array $query = null;
    private ?array $args = null;
    private ?RouteContext $route = null;
    private ?User $user;

    protected $method = 'GET';

    protected $session;

    public function __construct(
        protected ContainerInterface $container
    ) {
        $this->session = $this->container->get(Session::class);
    }

    private function setResponse(ResponseInterface $response): self
    {
        $this->response = $response;
        return $this;
    }

    protected function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    private function setRequest(ServerRequestInterface $request): self
    {
        $this->request = $request;
        return $this;
    }

    protected function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    private function setQuery(): self
    {
        $this->query = $this->getRequest()->getQueryParams();
        return $this;
    }

    protected function getQuery(): ?array
    {
        return $this->query;
    }

    protected function getQueryPart(string $name): ?string
    {
        if(isset($this->getQuery()[$name])) {
            return $this->getQuery()[$name];
        }
        return null;
    }

    private function setArgs(array $args): self
    {
        $this->args = $args;
        return $this;
    }

    protected function getArgs(): ?array
    {
        return $this->args;
    }

    /**
     * getArg
     *
     * Get a route argument as defined in routes.php
     *
     * Returns `null` if the argument was not found
     *
     * @param string $key
     * @return mixed
     */
    protected function getArg(string $key): mixed
    {
        if(isset($this->getArgs()[$key])) {
            return $this->getArgs()[$key];
        }
        return null;
    }

    private function setRoute(): self
    {
        $this->route = RouteContext::fromRequest($this->getRequest());
        return $this;
    }

    public function getRoute(): RouteContext
    {
        return $this->route;
    }

    /**
     * isPOST
     *
     * Returns whether ot not the current request is POST or not
     *
     * @return boolean
     */
    public function isPOST(): bool
    {
        return ('POST' === $this->method ? true : false);
    }

    /**
     * __invoke
     *
     * The method called by Slim when invoked by the router. Instantiates a
     * bunch of Controller properties that are heavily used elsewhere.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args = []): ResponseInterface
    {
        $this->setResponse($response);
        $this->setRequest($request);
        $this->setQuery();
        $this->setArgs($args);
        $this->setRoute();
        $this->setUser($request->getAttribute('user'));
        $this->permissionCheck();
        $this->method = $this->request->getMethod();
        return $this->action();
    }

    /**
     * render
     *
     * Renders the given `$template` file with the given `$data`.
     * Returns a JSON string if the `json` attribute is set on the URL
     *
     * @param string $template
     * @param array $data
     * @return ResponseInterface
     */
    protected function render(string $template, array $data = []): ResponseInterface
    {
        if(isset($_GET['json'])) {
            return $this->json($data);
        }
        $twig = $this->container->get(Twig::class);
        return $twig->render($this->getResponse(), $template, $data);
    }

    /**
     * json
     *
     * Returns the given `$data` as a JSON string
     *
     * @param array $data
     * @return ResponseInterface
     */
    protected function json(array $data): ResponseInterface
    {
        $response = $this->response->withHeader("Content-Type", "application/json");
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response;
    }

    /**
     * getUriForRoute
     *
     * Gets the full, complete URI (including protocol) for the given `$route`
     * name.
     *
     * Attempts to remove the port from the URI.
     *
     * @param string $route
     * @return string
     */
    protected function getUriForRoute(string $route, $args = []): string
    {
        $router = $this->container->get(RouteParserInterface::class);
        $uri = $router->fullUrlFor(
            $this->getRequest()->getUri()->withPort(null),
            $route,
            $args
        );
        if ((isset($_SERVER['HTTPS']) && 'On' === $_SERVER['HTTPS']) || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
            $uri = str_replace('http://', 'https://', $uri);
        }
        return $uri;
    }

    /**
     * routeRedirect
     *
     * For the given `$route`, generate a full uri for the route to redirect to.
     * Passes the result on to @method string redirect()
     *
     * @param string $route
     * @return ResponseInterface
     */
    protected function routeRedirect(string $route, array $args = []): ResponseInterface
    {
        return $this->redirect($this->getUriForRoute($route, $args));
    }

    /**
     * Returns a `ResponseInterface` instance with an HTTP 301 redirect to the
     * given `$uri`
     *
     * @param string $uri
     * @return ResponseInterface
     */
    protected function redirect(string $uri): ResponseInterface
    {
        return $this->response->withStatus(301)->withHeader('Location', $uri);
    }

    /**
     * getUser
     *
     * Returns an instance of the current logged in user, or null if there is
     * no logged in user.
     *
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Verify that the current logged in user has the permission required
     * by the request attribute. Throws an exception if this check fails, or
     * if the user is not logged in.
     *
     * @return void
     */
    private function permissionCheck(): void
    {
        $user = $this->getUser();
        $activeUser = $this->getRequest()->getAttribute('authenticated');
        if($activeUser) {
            if(!$user) {
                throw new StatbusUnauthorizedException("You must be logged in to access this", 403);
            }
        }
        $require = $this->getRequest()->getAttribute('require');
        if($require) {
            if($require && !$user) {
                $this->session->set('authRedirect', (string) $this->getRequest()->getUri()->withPort(null));
                throw new StatbusUnauthorizedException("You must be logged in to access this", 403);
            } elseif ($require && !$user->has($require)) {
                throw new StatbusUnauthorizedException("You do not have permission to access this", 403);
            }
        }

    }

    /**
     * addSuccessMessage
     *
     * Adds a success (green) message to the Session global's flash bag.
     *
     * @link https://symfony.com/doc/current/session.html#flash-messages
     *
     * @param string $message
     * @return self
     */
    public function addSuccessMessage(string $message): self
    {
        $this->session->getFlashbag()->add('success', $message);

        return $this;
    }

    /**
     * addErrorMessage
     *
     * Adds a success (green) message to the Session global's flash bag.
     *
     * @link https://symfony.com/doc/current/session.html#flash-messages
     *
     * @param string $message
     * @return self
     */
    public function addErrorMessage(string $message): self
    {
        $this->session->getFlashbag()->add('danger', $message);
        return $this;
    }

    /**
     * action
     *
     * Abstract function extended by controllers. Generally executes another
     * method from this class (i.e. `$this->render` or `$this->json`) and
     * returns a ResponseInterface instance
     *
     * @return ResponseInterface
     */
    abstract public function action(): ResponseInterface;
}

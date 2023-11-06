<?php

namespace App\Middleware;

use App\Controller\Controller;
use App\Factory\LoggerFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Exception;
use Fig\Http\Message\StatusCodeInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Slim\Psr7\Response;
use Slim\Views\Twig;
use Throwable;

class ExceptionMiddleware extends Controller implements MiddlewareInterface
{
    private $settings;

    private $logger;

    private $user;

    public function __construct(
        private ContainerInterface $containerInterface
    ) {
        $this->settings = $containerInterface->get('settings')['error'];
        $logger = $log = new Logger('stdout');
        $log->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));
        $this->logger = $logger;
        $this->user = $containerInterface->get('User');
    }

    public function action(): ResponseInterface
    {
        return $this->getResponse();
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $twig = $this->containerInterface->get(Twig::class);
        try {
            return $handler->handle($request);
        } catch (Exception $exception) {
            $url = (string)$request->getUri()->withPort(null);
            $path = explode('/', parse_url($url)['path']);
            if(404 === $exception->getCode() && str_contains(end($path), '.')) {
                return new Response(StatusCodeInterface::STATUS_NOT_FOUND);
            }
            $response = new Response(500);
            if ($this->settings['log_errors']) {
                $error = $this->getErrorDetails($exception, $this->settings['log_error_details']);
                $error['method'] = $request->getMethod();
                $error['url'] = $url;
                $error['ip'] = $_SERVER['REMOTE_ADDR'];
                $error['user'] = $this->user ? $this->user->getCkey() : null;
                $this->logger->error($exception->getMessage(), $error);
            }
            return $twig->render(
                $response,
                'error.html.twig',
                [
                    'error' => $exception,
                    'class' => str_replace("\\", "/", get_class($exception)),
                    'display_error_details' => $this->settings['display_error_details'],

                ],
            );
        }
    }
    private function getErrorDetails(Throwable $exception, bool $displayErrorDetails): array
    {
        if ($displayErrorDetails === true) {
            return [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'previous' => $exception->getPrevious(),
                'trace' => $exception->getTrace(),
            ];
        }

        return [
            'message' => $exception->getMessage(),
        ];
    }
}

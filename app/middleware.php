<?php

use App\Middleware\ExceptionMiddleware;
use App\Middleware\UserMiddleware;
use Slim\App;
use Slim\Views\TwigMiddleware;
use Middlewares\TrailingSlash;
use Slim\Views\Twig;
use RKA\Middleware\IpAddress;
use Zeuxisoo\Whoops\Slim\WhoopsMiddleware;

return function (App $app) {
    $app->addBodyParsingMiddleware();
    $app->addRoutingMiddleware();
    $app->add(TwigMiddleware::create($app, $app->getContainer()->get(Twig::class)));
    $app->add(ExceptionMiddleware::class);
    $app->add(UserMiddleware::class);
    $app->add(new TrailingSlash());
    $settings = $app->getContainer()->get('settings');
    $app->add(new IpAddress(checkProxyHeaders: $settings['check_proxy_headers'], trustedProxies: [], headersToInspect: $settings['proxy_headers']));

    // $app->add(new WhoopsMiddleware());
};

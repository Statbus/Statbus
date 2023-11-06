<?php

use Slim\App;
use Slim\Views\TwigMiddleware;
use Middlewares\TrailingSlash;
use Slim\Middleware\ErrorMiddleware;
use Slim\Views\Twig;

return function (App $app) {
    $app->addBodyParsingMiddleware();
    $app->addRoutingMiddleware();
    $app->add(TwigMiddleware::create($app, $app->getContainer()->get(Twig::class)));
    $app->add(new TrailingSlash());
    $app->add(ErrorMiddleware::class);
};

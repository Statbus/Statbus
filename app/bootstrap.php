<?php

function pick($list)
{
    if (is_string($list)) {
        $list = explode(',', $list);
    } elseif (is_object($list)) {
        $list = json_decode(json_encode($list), true);
    }
    return $list[floor(rand(0, count($list) - 1))];
}

use DI\ContainerBuilder;

require_once(__DIR__."/encoding.php");
require_once(__DIR__."/../vendor/autoload.php");

$containerBuilder = (new ContainerBuilder())
    ->useAttributes(true)
    ->addDefinitions(__DIR__ . '/container.php')
    ->build();

return $containerBuilder->get(App::class);

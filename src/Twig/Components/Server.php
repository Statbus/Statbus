<?php

namespace App\Twig\Components;

use App\Entity\Server as EntityServer;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Server
{
    public EntityServer $server;
}

<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelInterface;

final class KillswitchListener
{
    public function __construct(
        private KernelInterface $kernel
    ) {}

    #[AsEventListener]
    public function onRequestEvent(RequestEvent $event): void
    {
        $projectDir = $this->kernel->getProjectDir();
        if (!is_file(Path::join($projectDir, 'STATBUS_ENABLED'))) {
            $event->setResponse(new Response(
                'Statbus is disabled',
                Response::HTTP_SERVICE_UNAVAILABLE
            ));
        }
    }
}

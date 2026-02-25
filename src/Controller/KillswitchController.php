<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;

final class KillswitchController extends AbstractController
{
    public function __construct(
        private KernelInterface $kernel,
        private Filesystem $fs,
        #[Autowire(env: 'string:KILL_CODE')]
        private string $killCode
    ) {}

    #[Route('/kill', name: 'killswitch')]
    public function index(Request $request): Response
    {
        if ('POST' === $request->getMethod()) {
            if ($request->request->get('killCode', null) === $this->killCode) {
                $projectDir = $this->kernel->getProjectDir();
                $this->fs->remove(Path::join($projectDir, 'STATBUS_ENABLED'));
                die();
            }
        }
        return $this->render('kill.html.twig');
    }
}

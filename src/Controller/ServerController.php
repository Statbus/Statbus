<?php

namespace App\Controller;

use App\Service\ServerInformationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/servers')]
class ServerController extends AbstractController
{
    public function __construct(
        private ServerInformationService $serverInformationService
    ) {}

    #[Route('', name: 'app.servers')]
    public function index(): Response
    {
        return $this->render('server/index.html.twig');
    }
}

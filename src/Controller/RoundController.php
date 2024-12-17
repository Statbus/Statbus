<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/round', name: 'round')]
class RoundController extends AbstractController
{

    #[Route('/{id}', name: '')]
    public function index(int $id): Response
    {
        return $this->render('round/index.html.twig');
    }

    #[Route('/{id}/popover', name: '.popover')]
    public function popover(int $id): Response
    {
        return $this->render('round/popover.html.twig', [
            'round' => $id
        ]);
    }
}

<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/round', name: 'round')]
class RoundController extends AbstractController
{

    #[Route('/{round}', name: '')]
    public function index(int $round): Response
    {
        return $this->render('round/index.html.twig');
    }

    #[Route('/{round}/popover', name: '.popover')]
    public function popover(int $round): Response
    {
        return $this->render('round/popover.html.twig', [
            'round' => $round
        ]);
    }
}

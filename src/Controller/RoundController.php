<?php

namespace App\Controller;

use App\Repository\RoundRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RoundController extends AbstractController
{

    public function __construct(
        private RoundRepository $roundRepository
    ) {}

    #[Route('/rounds/{page}', name: 'rounds')]
    public function index(int $page = 1): Response
    {
        $pagination = $this->roundRepository->getRounds($page);
        return $this->render('round/index.html.twig', [
            'pagination' => $pagination
        ]);
    }

    #[Route('/round/{round}', name: 'round')]
    public function round(int $round): Response
    {
        return $this->render('round/round.html.twig', [
            'round' => $round
        ]);
    }

    #[Route('/round/{round}/popover', name: 'round.popover')]
    public function popover(int $round): Response
    {
        return $this->render('round/popover.html.twig', [
            'round' => $round
        ]);
    }
}

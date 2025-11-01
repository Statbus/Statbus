<?php

namespace App\Controller;

use App\Attribute\FeatureEnabled;
use App\Repository\PlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[FeatureEnabled('tgdb.newplayers')]
#[IsGranted('ROLE_BAN')]
#[Route('/newplayers', name: 'newplayers')]
class NewPlayerController extends AbstractController
{
    public function __construct(
        private PlayerRepository $playerRepository
    ) {}

    #[Route('', name: '')]
    public function index(): Response
    {
        $data = $this->playerRepository->getNewPlayers();
        return $this->render('newplayers/index.html.twig', [
            'data' => $data
        ]);
    }
}

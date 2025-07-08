<?php

namespace App\Controller;

use App\Repository\PlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/search', name: 'search', methods: ['POST'])]
class SearchController extends AbstractController
{
    public function __construct(
        private PlayerRepository $playerRepository
    ) {}

    #[Route('', name: '')]
    public function index(Request $request): Response
    {
        $term = $request->toArray()['term'];
        $data['ckeys'] = $this->playerRepository->search($term);
        return $this->json([
            'term' => $term,
            'results' => [...$data['ckeys']]
        ]);
    }
}

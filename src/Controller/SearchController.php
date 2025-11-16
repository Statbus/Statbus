<?php

namespace App\Controller;

use App\Repository\ManifestRepository;
use App\Repository\PlayerRepository;
use App\Service\FeatureFlagService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/search', name: 'search', methods: ['POST'])]
class SearchController extends AbstractController
{
    public function __construct(
        private PlayerRepository $playerRepository,
        private ManifestRepository $manifestRepository,
        private FeatureFlagService $feature
    ) {}

    #[Route('', name: '')]
    public function index(Request $request): Response
    {
        $term = $request->toArray()['term'];
        if (
            $this->feature->isEnabled('players.public')
            || $this->isGranted('ROLE_ADMIN')
        ) {
            $data['ckey'] = $this->playerRepository->search($term);
            if ($this->feature->isEnabled('manifest')) {
                $data['character'] = $this->manifestRepository->search($term);
            }
        }
        return $this->json([
            'term' => $term,
            'results' => [...$data['ckey'], ...$data['character']]
        ]);
    }
}

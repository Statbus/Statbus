<?php

namespace App\Controller;

use App\Service\Death\HeatmapService;
use App\Service\Map\MapService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/death', name: 'death')]
final class DeathController extends AbstractController
{
    public function __construct(
        private HeatmapService $heatmapService,
        private MapService $mapService,
        private readonly string $outputDir
    ) {}

    #[Route('', name: '')]
    public function index(): Response
    {
        return $this->render('death/index.html.twig', [
            'controller_name' => 'DeathController'
        ]);
    }

    #[Route('/heatmap/{map}/{z}', name: '.heatmap')]
    public function heatmap(string $map, int $z = 2): Response
    {
        $maps = $this->mapService->getAvailableMaps();
        $mapData = $this->mapService->getMap($map);
        $deaths = $this->heatmapService->generateHeatmap($map, $z, true);
        return $this->render('death/heatmap.html.twig', [
            'maps' => $maps,
            'map' => $mapData,
            'deaths' => $deaths,
            'z' => $z
        ]);
    }
}

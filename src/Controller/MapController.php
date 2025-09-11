<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MapController extends AbstractController
{
    public function __construct(
        private readonly string $outputDir
    ) {}

    #[Route('/map', name: 'maps')]
    public function index(): Response
    {
        return $this->render('map/index.html.twig', [
            'maps' => $maps
        ]);
    }
}

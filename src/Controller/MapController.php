<?php

namespace App\Controller;

use App\Service\Map\MapRendererService;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MapController extends AbstractController
{
    public function __construct(
        private MapRendererService $mapRendererService
    ) {}

    #[Route('/map/{map}', name: 'map')]
    public function parse(Request $request, ?string $map = null): Response
    {
        $data = null;
        $stats = null;
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator('/tg/_maps/map_files')
        );
        $file = $request->request->get('file', null);
        if ($file) {
            $file = pathinfo($file);
            $file = $file['filename'] . '.' . $file['extension'];
            return $this->redirectToRoute('map', ['map' => $file]);
        }
        foreach ($files as $file) {
            if ('dmm' === $file->getExtension()) {
                $mapFiles[] = str_replace('/tg/', '', $file->getRealpath());
            }
            if ($map && str_ends_with($file->getRealpath(), $map)) {
                $data = $this->mapRendererService->getFromMapFile($file);
                foreach ($data['map'] as $m) {
                    foreach ($m as $x => $row) {
                        foreach ($row as $y => $col) {
                            $areas[] = $data['symbols'][$col]->area;
                            $turfs[] = $data['symbols'][$col]->turf;
                        }
                    }
                }
                $areas = array_count_values($areas);
                $turfs = array_count_values($turfs);
                arsort($areas);
                arsort($turfs);
                $stats = [
                    'areas' => $areas,
                    'turfs' => $turfs
                ];
            }
        }
        return $this->render('map.html.twig', [
            'maps' => $mapFiles,
            'data' => $data,
            'stats' => $stats
        ]);
    }
}
